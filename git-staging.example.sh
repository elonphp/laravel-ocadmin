#!/usr/bin/env bash
# ============================================================
# git-staging.example.sh -- 範本（TEMPLATE）
#
# 首次設定（每位開發者各自）：
#   1. cp git-staging.example.sh git-staging.sh
#   2. 編輯 git-staging.sh，把 MY_BRANCH 改成你自己的 branch
#   3. 確認 REMOTE 是本專案的 remote 名稱
#   4. chmod +x git-staging.sh（首次需要）
#   5. git-staging.sh 已 gitignored，不要 commit
#
# 為什麼要寫死 MY_BRANCH 而不是偵測 HEAD？
#   如果不小心 git switch 到別的 branch，「偵測 HEAD」會直接把錯的 branch
#   推到 staging。明碼指定 + 驗證 HEAD == MY_BRANCH 才擋得住。
#
# 流程：
#   1. 當前 branch == MY_BRANCH
#   2. fetch REMOTE
#   3. 防呆：偵測是否改寫了已 push 到 staging 的 commit（見下）
#   4. rebase --autostash REMOTE/staging（衝突自己解：git add → git rebase --continue，解完重跑）
#   5. push MY_BRANCH（--force-with-lease，rebase 會改 SHA）
#   6. FF push MY_BRANCH → REMOTE/staging
#
# 為什麼用 rebase 不用 merge？
#   rebase 只把你自己的 commit 重放到 staging 頂端，歷史線性、不堆 merge commit，
#   過時的 base commit 也會自動脫落。個人分支（各自一個 owner）rebase 是安全的。
#   git-sync.sh 用的也是同一步（只是不推），開工前 sync 跟收工前 publish 同一套。
#
# 分支模型：一人一條、以開發者自己命名（示範：johnbranch / peterbranch），長期存在。
#   步驟 5 的 --force-with-lease **安全的唯一前提就是「該分支只有一個 owner」**。
#   兩人共用同一條分支時，force push 會砍掉對方的 commit。
#   注意：rebase 之後你的分支也含同事的 commit，分支名不代表「只有我的工作」；
#   要查自己做了什麼用 git log --author，不要看分支。
#
# rebase 的 footgun（步驟 3 防呆要擋的）：
#   rebase 把遠端 staging 當「不可變基底」。所以「已經 push 到 staging 的 commit」
#   無法用 amend / squash 取代——你改寫後再跑本腳本，rebase 會把改寫的版本「疊到」
#   遠端那筆舊 commit 之上（內容相同則判為空丟掉），結果 staging / main 冒出重複的
#   同標題 commit。
#   規矩：commit 一旦透過本腳本 push 出去，就別再 amend / squash 它；要補東西就疊新
#   commit。真要改寫已 push 的歷史，請繞過本腳本、手動 force-push 對齊分支。
#   步驟 3 會在分岔時偵測 (a) tree 與 staging 相同但 SHA 不同，或 (b) 本地/遠端獨有
#   commit 同 subject，命中就停下提示。確定是巧合用 STAGING_ALLOW_REWRITE=1 略過。
#
# 為什麼用 --autostash（不檢查 working tree 乾淨）？
#   --autostash 會在 rebase 前把未 commit 的草稿收起來、rebase 後再放回，全程不會被
#   推到 staging，所以寫一半也能同步。rebase 或 autostash-pop 遇到真衝突時 git 一樣
#   會停下來要你手動解，不會自動丟掉任何一邊。
#
# 多人共開安全：步驟 4 先把遠端 staging（含同事 commit）rebase 進來，步驟 6 FF
# 一定成立，永不覆蓋同事的 push。**不提供 --force 模式**。
#   競態：若同事在你 fetch 到 FF push 之間推了東西，步驟 6 會以 non-fast-forward
#   失敗——這是正確行為不是 bug，重跑本腳本即可（會重新 fetch + rebase）。
#
# 跟 git-main 的差別：
#   - 本檔只更新 staging，不觸發部署。可一天多次跑。
#   - git-main.sh 才會把 staging 推到 main。
#   - 誰能跑：本檔**所有開發者**都能跑；git-main 只由**專案負責人**執行。
#
# ⚠️ 核心紀律：跑本腳本 = 宣告「這段可以上線」，不是「存檔備份」。
#
#   理由是 git-main 的推送方式：main 會變成**跟 staging 一模一樣**，沒有辦法只挑
#   一部分上線。
#
#   後果：只要有人把半成品推上 staging，負責人就無法在不帶上那個半成品的前提下
#   部署任何東西——包含別人的急件修正。整條線被卡住，而且沒有任何機制會擋，
#   只有這條紀律。
#
#   所以：寫一半的東西 commit 在自己分支上就好，先別跑本腳本。要暫存進度用
#   git commit（本地）或 git-sync.sh（只拉不推），不要用 git-staging。
#
#   推論：staging 上的每一筆，都應該是「現在按下 git-main 也不會出事」的狀態。
# ============================================================

MY_BRANCH="YOUR_BRANCH_HERE"

# 本專案的 remote 名稱。衍生專案若用別的名稱（如 origin）改這裡。
REMOTE="${REMOTE:-elonphp}"

set -euo pipefail

cd "$(dirname "$0")"

if [ "$MY_BRANCH" = "YOUR_BRANCH_HERE" ]; then
    echo "錯誤：請先編輯本檔頂端的 MY_BRANCH 設成你自己的 branch 名稱。"
    exit 1
fi

# 驗證當前 branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "$MY_BRANCH" ]; then
    echo "錯誤：預期在 $MY_BRANCH，目前在 $CURRENT_BRANCH。"
    echo "請先：git switch $MY_BRANCH"
    exit 1
fi

echo "=== 同步 $MY_BRANCH 到遠端 staging ==="
echo

echo "=== 1. fetch $REMOTE ==="
git fetch "$REMOTE"

echo
echo "=== 2. 防呆：偵測是否改寫了已 push 到 staging 的 commit ==="
# 原理見檔頭「rebase 的 footgun」。與 staging 分岔時偵測：
#   (a) 整棵 tree 與 staging 相同但 SHA 不同 → 內容一致的歷史改寫（例如 squash 後重跑）
#   (b) 本地獨有與遠端獨有 commit 出現相同 subject → 改了內容但標題沿用（例如 amend 補一段）
# 確定只是不同改動剛好同標題 → 設 STAGING_ALLOW_REWRITE=1 重跑略過。
if [ "${STAGING_ALLOW_REWRITE:-}" != "1" ] && ! git merge-base --is-ancestor "$REMOTE/staging" HEAD; then
    REWRITE_HINT=""
    if [ "$(git rev-parse 'HEAD^{tree}')" = "$(git rev-parse "$REMOTE/staging^{tree}")" ]; then
        REWRITE_HINT="本地與遠端 staging 內容完全相同、只有歷史不同（典型 amend/squash 後重跑）。"
    fi
    DUP_SUBJECTS=$(git log --format='%s' "HEAD..$REMOTE/staging" \
        | grep -Fxf <(git log --format='%s' "$REMOTE/staging..HEAD") || true)
    if [ -n "$REWRITE_HINT" ] || [ -n "$DUP_SUBJECTS" ]; then
        echo "⚠ 偵測到你可能改寫了『已 push 到 staging』的 commit。"
        [ -n "$REWRITE_HINT" ] && echo "    $REWRITE_HINT"
        if [ -n "$DUP_SUBJECTS" ]; then
            echo "    下列標題在本地與遠端 staging 各出現（疑似同一筆被改寫）："
            echo "$DUP_SUBJECTS" | sed 's/^/        /'
        fi
        echo
        echo "  直接 rebase 不會取代遠端那筆，只會疊成重複的同標題 commit。"
        echo "  改寫已 push 的歷史時，請繞過本腳本，手動 force-push 對齊："
        echo "      git push $REMOTE $MY_BRANCH --force-with-lease"
        echo "      git push $REMOTE $MY_BRANCH:refs/heads/staging --force-with-lease"
        echo
        echo "  確定只是不同改動剛好同標題 → 重跑時略過防呆："
        echo "      STAGING_ALLOW_REWRITE=1 ./git-staging.sh"
        exit 1
    fi
fi

echo
echo "=== 3. rebase $MY_BRANCH 到 $REMOTE/staging 之上 ==="
if ! git rebase --autostash "$REMOTE/staging"; then
    echo
    echo "rebase 遇到衝突。解決檔案後：git add <檔案> → git rebase --continue，完成後重跑 git-staging.sh。"
    echo "要放棄：git rebase --abort"
    exit 1
fi

echo
echo "=== 4. push $MY_BRANCH ==="
git push "$REMOTE" "$MY_BRANCH" --force-with-lease

echo
echo "=== 5. FF push $MY_BRANCH → $REMOTE/staging ==="
git push "$REMOTE" "$MY_BRANCH:refs/heads/staging"

echo
echo "=== 完成。staging 已同步。要推上 main 由負責人跑 git-main ==="
