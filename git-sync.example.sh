#!/usr/bin/env bash
# ============================================================
# git-sync.example.sh -- 範本（TEMPLATE）
#
# 用途：
#   開工前執行。把遠端 staging（含同事已整合的工作）rebase 到自己的分支頂端，
#   讓新工作疊在最新進度之上。**只拉不推**。
#   發佈時用 git-staging.sh —— 它跑的是同一個 rebase 步驟，之後再把分支推回 staging。
#
# 首次設定（每位開發者各自）：
#   1. cp git-sync.example.sh git-sync.sh
#   2. 編輯 git-sync.sh，把 MY_BRANCH 改成你自己的 branch（如 johnbranch / peterbranch —— 示範用的中性名稱，實際請用你自己的）
#   3. 確認 REMOTE 是本專案的 remote 名稱
#   4. chmod +x git-sync.sh（首次需要）
#   5. git-sync.sh 已 gitignored，不要 commit
#
# 流程：
#   1. 當前 branch == MY_BRANCH
#   2. fetch REMOTE
#   3. rebase --autostash REMOTE/staging
#
# 為什麼用 rebase 不用 merge？
#   rebase 只把你自己的 commit 重放到 staging 頂端，歷史線性、不堆 merge commit，
#   過時的 base commit 也會自動脫落。個人分支（各自一個 owner）rebase 是安全的。
#
# 為什麼用 --autostash？
#   會在 rebase 前把未 commit 的草稿收起來、rebase 後再放回，寫一半也能同步。
#   遇到真衝突時 git 一樣會停下來要你手動解，不會自動丟掉任何一邊。
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

echo "=== 同步 $MY_BRANCH 與遠端 staging（rebase）==="
echo

echo "=== 1. fetch $REMOTE ==="
git fetch "$REMOTE"

echo
echo "=== 2. rebase $MY_BRANCH 到 $REMOTE/staging 之上 ==="
if ! git rebase --autostash "$REMOTE/staging"; then
    echo
    echo "rebase 遇到衝突。解決檔案後：git add <檔案> → git rebase --continue。"
    echo "要放棄：git rebase --abort"
    exit 1
fi

echo
echo "=== 完成。$MY_BRANCH 已疊到最新 staging 之上。開始開發，要發佈再跑 git-staging ==="
