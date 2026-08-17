#!/usr/bin/env bash
# ============================================================
# git-main.example.sh -- 範本（TEMPLATE）
#
# 首次設定：
#   1. cp git-main.example.sh git-main.sh
#   2. 確認 REMOTE 是本專案的 remote 名稱
#   3. chmod +x git-main.sh
#   4. git-main.sh 已 gitignored，可自由客製（如加上開發者署名 echo）
#
# ⚠️ 誰能跑：**只有專案負責人**。git-staging 是所有開發者都能跑，本檔不是。
#    原因見下方「全有全無」——本檔會把整個 staging 原封不動變成 main。
#
# 用途：把 REMOTE/staging 推到 main。
#   有正式區的衍生專案：main 即部署來源，推完接著跑 deploy-finish.sh。
#   ocadmin 範本自身無部署目標，main 就只是對外公開的發佈分支，推完即結束。
#
# 流程：
#   1. fetch REMOTE（前置檢查：REMOTE/main 已等於 staging → 中止）
#   2. 備份：old-main 遞增合併（每次多一個 merge 節點，永不丟資料）
#      - 第一次（無遠端 old-main）：直接 push REMOTE/main → old-main 啟動
#      - 後續：建 merge commit（parents = old-main + main，tree = main），FF push
#   3. push REMOTE/staging → REMOTE/main
#
# 所有 ref 都用遠端 ref，本地 staging 分支不參與。
#
# ------------------------------------------------------------
# 為什麼步驟 3 不加 --force？
#
#   正常路徑本來就是 fast-forward，不需要 force：每次推完 main == staging，
#   之後 staging 只會在其上往前長（git-staging 的步驟 5 是 FF push），
#   所以 staging 永遠是 main 的後代。
#
#   不加 --force 的好處：**main 可以開 branch protection 鎖 force push**。
#   加了 force 就等於永久要求 main 不受保護，只為了那個極少發生的情況。
#
#   何時真的需要 force？只有「main 曾被退回舊 commit」之後（見下方 Rollback）。
#   此時 staging 不再是 main 的後代，本腳本步驟 3 會以 non-fast-forward 失敗
#   ——這是**正確的警示**，不是 bug。處理方式：確認要覆蓋後手動推一次
#       git push <remote> <remote>/staging:refs/heads/main --force
#   （main 有開 branch protection 的話，要先暫時放行 force push）
# ------------------------------------------------------------
#
# 推送前確認：
#   - staging 上沒有半成品 commit（見 git-staging.sh 檔頭「核心紀律」）
#   - 本機跑過、確認沒問題
#
# Rollback：
#   單步：git push <remote> <remote>/old-main^2:refs/heads/main --force
#         （old-main HEAD 是 merge commit；^2 = 第二個 parent = 上一次 main）
#   多步：git log <remote>/old-main --merges --first-parent 找目標 merge commit，
#         然後 push 該 commit 的 ^2
#   rollback 之後下一次跑本腳本會如上所述 non-fast-forward 失敗，屬預期。
#
# 用法：
#   chmod +x git-main.sh   # 首次須賦予執行權限
#   ./git-main.sh
# ============================================================

# 本專案的 remote 名稱。衍生專案若用別的名稱（如 origin）改這裡。
REMOTE="${REMOTE:-elonphp}"

set -euo pipefail

cd "$(dirname "$0")"

echo "=== 1. fetch $REMOTE ==="
git fetch "$REMOTE"

MAIN_SHA=$(git rev-parse "$REMOTE/main")
STAGING_SHA=$(git rev-parse "$REMOTE/staging")
if [ "$MAIN_SHA" = "$STAGING_SHA" ]; then
    echo
    echo "提示：main 已等於 staging，無新 commit 可推送，已中止。"
    exit 0
fi

# 前置檢查：staging 必須是 main 的後代，否則步驟 3 的 FF push 會失敗。
# 提早在這裡講清楚原因，比讓 git 丟一句 non-fast-forward 好懂。
if ! git merge-base --is-ancestor "$MAIN_SHA" "$STAGING_SHA"; then
    echo
    echo "錯誤：staging 不是 main 的後代，無法 fast-forward。"
    echo "  常見原因：main 曾被 rollback 退回舊 commit。"
    echo "  確認要用 staging 覆蓋 main 後，手動推一次："
    echo "      git push $REMOTE $REMOTE/staging:refs/heads/main --force"
    echo "  （main 若有開 branch protection，需先暫時放行 force push）"
    exit 1
fi

echo
echo "=== 2. 備份：old-main 遞增合併 ==="
OLD_MAIN_SHA=$(git rev-parse "$REMOTE/old-main" 2>/dev/null || echo "")
DATE_UTC=$(date -u +%Y%m%dT%H%M%SZ)
MAIN_SHORT=$(git rev-parse --short "$REMOTE/main")
MAIN_TREE=$(git rev-parse "$REMOTE/main^{tree}")

if [ -z "$OLD_MAIN_SHA" ]; then
    echo "提示：遠端尚無 old-main，bootstrap 為 $REMOTE/main。"
    git push "$REMOTE" "$REMOTE/main:refs/heads/old-main"
else
    NEW_OLD_SHA=$(git commit-tree "$MAIN_TREE" \
        -p "$OLD_MAIN_SHA" \
        -p "$MAIN_SHA" \
        -m "backup: archive main $MAIN_SHORT (deploy $DATE_UTC)")
    git push "$REMOTE" "$NEW_OLD_SHA:refs/heads/old-main"
fi

echo
echo "=== 3. 推送 $REMOTE/staging → $REMOTE/main ==="
git push "$REMOTE" "$REMOTE/staging:refs/heads/main"

echo
echo "=== 完成 ==="
echo "有正式區的專案：接著在正式區跑 ./deploy-finish.sh"
echo "  （composer install / db:transition / optimize:clear，git pull 不會做這些）"
echo "Rollback：git push $REMOTE $REMOTE/old-main^2:refs/heads/main --force"
