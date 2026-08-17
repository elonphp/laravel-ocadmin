#!/usr/bin/env bash
# ============================================================
# deploy-finish.example.sh -- 範本（TEMPLATE）
#
# 首次設定（每位開發者各自）：
#   1. cp deploy-finish.example.sh deploy-finish.sh
#   2. chmod +x deploy-finish.sh
#   3. deploy-finish.sh 已 gitignored，可自由客製
#      （PHP / COMPOSER 執行檔 / DEPLOY_OWNER / 要不要 down-up 包夾 / 署名 log）
#
# 用途：正式區 deploy 後置收尾（Linux 版）。
#
# ocadmin 範本自身無部署目標，本檔是給**衍生專案**複製後使用的範本。典型情境：
# 推送到正式區分支後由 webhook（或 cron / 手動）在正式區 git pull —— 那一步
# 只拉 code，以下幾步它不會做，pull 完 SSH 進正式區站台根目錄跑這支即可。
#
# 冪等（每次 deploy 都可無腦跑、不必判斷該跑哪步）：
#   - composer install：composer.lock 沒變 → 自己秒跳過
#   - db:transition：無 pending → no-op
#   - optimize:clear / optimize：永遠安全
#   - 修正擁有者：預設不啟用（見下方 DEPLOY_OWNER）
#
# ------------------------------------------------------------
# 執行檔：預設吃 PATH 上的 php / composer，可用環境變數覆寫：
#   PHP=/opt/plesk/php/8.5/bin/php ./deploy-finish.sh
#
# ⚠️ 主機把 CLI 裝成版本化名稱（php85 / composer85 之類）而 `php` 根本不在
# PATH 上，是常見情況。此時請直接改下方 PHP / COMPOSER 的預設值。
#
# ⚠️ COMPOSER 覆寫時務必指向 composer.phar 本體，或 composer85 這類「已綁好
# PHP 版本」的執行檔，**不要指向 PATH 上的 composer**。各家面板放的多半是一支
# shell wrapper，shebang 為 #!/usr/bin/env php；主機沒有 `php` 時它直接失敗。
# 更麻煩的是「用 php85 去跑那支 wrapper」——PHP 拿到非 PHP 內容會把 shell
# script 當純文字原樣印出、然後 exit 0，於是 composer install 靜默沒跑，
# 腳本一路往下、最後仍顯示「收尾完成」。踩過一次很難聯想。
# ------------------------------------------------------------
#
# ------------------------------------------------------------
# 前提：跑之前先確認 .env 已是最新。
#
# 步驟 4 的 optimize 會產生 bootstrap/cache/config.php，該檔一旦存在，
# Laravel 就「完全不讀 .env」。若順序是「先 deploy（建快取）→ 才改 .env」，
# 新設定不會生效，而且是靜默退回 config/*.php 的預設值、不會報錯。
#
# 實際踩過：.env 明明是 MAIL_MAILER=smtp，舊快取讓它退回預設的 log driver，
# 寄信時完全沒有例外、畫面顯示「寄送成功」，信卻只被寫進
# storage/logs/laravel.log（可用 From: Laravel <hello@example.com> 這行辨識）。
#
# deploy 後才動 .env 的話，補清快取並驗證：
#   php artisan optimize:clear
#   php artisan tinker --execute="echo config('mail.default');"
# ------------------------------------------------------------
#
# 秒級窗口：pull 完到 transition 跑完之間，新 code 已落地但 DB 未對齊，
# app 可能短暫 SQL error / class not found / 403。資料量小可接受；要保險可
# 自行在前後加 "$PHP" artisan down / up 包夾。
#
# 用法：
#   chmod +x deploy-finish.sh   # 首次須賦予執行權限
#   ./deploy-finish.sh
# ============================================================

set -euo pipefail

cd "$(dirname "$0")"

# 主機的 CLI 若是 php85 / composer85 這類版本化名稱，直接改這兩行的預設值。
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

# ---- 環境專屬：步驟 5 的擁有者修正（範本預設留空 = 不啟用）----
#
# 只有「以 root 在 Linux 主機面板環境（如 Plesk）執行」時才需要。複製成
# deploy-finish.sh 後填入該站的 vhost 用戶（下方 my-account 為佔位字串，請換成
# 實際帳號；本範本進版控，勿把真實系統帳號寫在這裡）：
#   DEPLOY_OWNER="my-account:psacln"
#
# 該站實際的 vhost 用戶可用以下指令查出（在正式區以 root 執行）：
#   stat -c '%U:%G' /var/www/vhosts/<domain>/httpdocs
#
# 留空時步驟 5 直接略過，Windows / 一般開發區不必理會（也絕不會誤觸 chown）。
DEPLOY_OWNER="${DEPLOY_OWNER:-}"
DEPLOY_DIR_GROUP="${DEPLOY_DIR_GROUP:-psaserv}"

echo "=== 1. composer install（composer.lock 沒變 → 秒跳過） ==="
$COMPOSER install --no-dev --optimize-autoloader

echo
echo "=== 2. db:transition（pending schema / 資料變更） ==="
"$PHP" artisan db:transition

echo
echo "=== 3. optimize:clear（讓新 route / config / view / lang 生效） ==="
"$PHP" artisan optimize:clear

echo
echo "=== 4. optimize（重新 cache，可選但建議） ==="
"$PHP" artisan optimize

echo
echo "=== 5. 修正擁有者（Linux + root + 已設 DEPLOY_OWNER 才執行） ==="
# 以 root 跑上面幾步時，composer / artisan 產生的檔案（vendor、bootstrap/cache、
# storage/framework）會變成 root 擁有，導致之後 PHP-FPM 寫不進去、optimize:clear
# 也刪不掉。這裡統一修回。
#
# guard 順序刻意如此：DEPLOY_OWNER 空值最先擋掉，Windows（Git Bash）連 uname /
# id 都不會走到。註：Git Bash 以系統管理員開啟時 id -u 可能回 0，單靠 id 判斷
# 會誤觸 chown，而 Git Bash 的 chown 對 NTFS 無效／報錯，配上 set -e 會中斷整支。
if [ -z "$DEPLOY_OWNER" ]; then
    echo "未設定 DEPLOY_OWNER，略過（Windows / 開發區的正常狀況）"
elif [ "$(uname -s)" != "Linux" ]; then
    echo "非 Linux（$(uname -s)），略過"
elif [ "$(id -u)" -ne 0 ]; then
    echo "非 root 執行，略過"
else
    # 結尾的 /. 只改「站台根目錄底下的內容」，不動該目錄本身。
    #
    # 少了 /. 會連目錄本身的 group 一起改掉（psaserv → psacln），造成全站
    # 403 Forbidden：psacln 不含 nginx / www-data，而站台根目錄 mode 為 750、
    # other 無 x 權限，web server 將無法進入目錄，連 public/index.php 都讀
    # 不到。此時 Laravel 根本不會被執行，laravel.log 完全沒有紀錄。
    #
    # 排查對照：ls -ld /var/www/vhosts/*/httpdocs（正常站台 group 皆為 psaserv）
    chown -R "$DEPLOY_OWNER" "$PWD/."
    chgrp "$DEPLOY_DIR_GROUP" "$PWD"
    echo "已修正擁有者：內容 → $DEPLOY_OWNER、目錄本身 → $DEPLOY_DIR_GROUP"
    ls -ld "$PWD"
fi

echo
echo "=== deploy 收尾完成 ==="
