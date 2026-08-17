@echo off
rem ============================================================
rem git-staging.example.bat -- TEMPLATE
rem
rem English-only on purpose: a .bat with CJK comments must be saved as
rem Big5/CP950 or cmd.exe shows mojibake; keeping this file pure ASCII
rem lets it stay UTF-8 and sidesteps the encoding trap entirely.
rem
rem Setup (per developer, first time):
rem   1. copy git-staging.example.bat git-staging.bat
rem   2. open git-staging.bat, change MY_BRANCH below to your branch
rem   3. check REMOTE matches this project's remote name
rem   4. git-staging.bat is gitignored - don't commit
rem
rem Why hardcode MY_BRANCH instead of detecting HEAD?
rem   If you accidentally switch to another branch and run the script,
rem   "detect HEAD" would happily push the wrong branch to staging.
rem   Hardcoding + verifying current == MY_BRANCH catches that.
rem
rem Flow:
rem   1. Verify current branch == MY_BRANCH (uncommitted work is fine)
rem   2. fetch REMOTE
rem   3. guard: detect rewrite of already-pushed commits (see below)
rem   4. rebase MY_BRANCH onto REMOTE/staging (resolve conflicts manually)
rem   5. push MY_BRANCH (--force-with-lease; rebase changed the SHAs)
rem   6. FF push MY_BRANCH to REMOTE/staging
rem
rem Why rebase instead of merge?
rem   Rebase replays only your commits on top of staging: history stays
rem   linear, no merge commits pile up, stale base commits are shed
rem   automatically. See also git-sync.bat (same step, run before you start).
rem
rem Branch model: one long-lived branch per person, named after them
rem (e.g. johnbranch / peterbranch - neutral placeholders, use your own).
rem   The --force-with-lease in step 5 is safe ONLY BECAUSE each branch has
rem   exactly one owner. Share a branch between two people and that push
rem   will destroy the other person's commits.
rem   Note: after rebasing, your branch also contains teammates' commits, so
rem   the branch name does not mean "only my work" - use git log --author.
rem
rem rebase footgun (what step 3 guards against):
rem   rebase treats remote staging as an IMMUTABLE base, so a commit already
rem   pushed to staging cannot be replaced by amend/squash - if you rewrite it
rem   and re-run, rebase stacks your rewrite ON TOP of the old remote commit
rem   (or drops it as empty when identical), producing duplicate same-title
rem   commits on staging/main.
rem   Rule: once a commit is pushed via this script, don't amend/squash it -
rem   add a new commit instead. To rewrite already-pushed history, bypass this
rem   script and force-push the refs manually.
rem   Step 3 flags, on divergence, (a) tree identical to staging but different
rem   SHA, or (b) a subject shared by local-only and remote-only commits. To
rem   skip when it's a real coincidence: set STAGING_ALLOW_REWRITE=1
rem
rem Why no working-tree-clean check?
rem   --autostash sets your uncommitted changes aside, rebases, then restores
rem   them; they are never pushed to staging. On a real conflict (rebase or
rem   autostash-pop), git stops for a manual fix.
rem
rem Multi-developer safety: step 4 brings remote staging (including teammates'
rem commits) into your branch before step 6 FF-push. Never overwrites teammate
rem commits. There is no --force mode by design.
rem   Race: if a teammate pushes between your fetch and your FF push, step 6
rem   fails as non-fast-forward. That is correct behaviour, not a bug - just
rem   re-run this script (it re-fetches and re-rebases).
rem
rem Difference from git-main:
rem   - This script only updates staging. Run it as often as you like.
rem   - git-main.bat is what pushes staging to main.
rem   - Who may run what: ANY developer may run this script; git-main.bat is
rem     for the project owner only.
rem
rem CORE RULE: running this script means "this is ready to ship", NOT "save my
rem work in progress".
rem
rem   Why: git-main makes main become EXACTLY staging. There is no way to
rem   ship only part of it.
rem
rem   Consequence: one half-finished commit on staging blocks the owner from
rem   shipping anything at all -- including someone else's urgent fix. Nothing
rem   enforces this; the rule is all there is.
rem
rem   So: keep unfinished work committed on your own branch and do NOT run
rem   this script yet. To save progress use git commit (local) or git-sync.bat
rem   (pull only, no push) -- not git-staging.
rem
rem   Corollary: every commit on staging should be in a state where pressing
rem   git-main right now would be safe.
rem ============================================================

set MY_BRANCH=YOUR_BRANCH_HERE

rem This project's remote name. Derivative projects using another name
rem (e.g. origin) should change this.
set REMOTE=elonphp

cd /d %~dp0
setlocal enabledelayedexpansion

if "%MY_BRANCH%"=="YOUR_BRANCH_HERE" (
    echo ERROR: edit MY_BRANCH at the top of this script first.
    exit /b 1
)

for /f %%i in ('git rev-parse --abbrev-ref HEAD') do set CURRENT_BRANCH=%%i

if not "%CURRENT_BRANCH%"=="%MY_BRANCH%" (
    echo ERROR: expected to be on %MY_BRANCH%, currently on %CURRENT_BRANCH%.
    echo Switch back with: git switch %MY_BRANCH%
    exit /b 1
)

echo === Sync %MY_BRANCH% to remote staging ===
echo.

echo === 1. fetch %REMOTE% ===
git fetch %REMOTE%
if errorlevel 1 exit /b 1
echo.

echo === 2. guard: detect rewrite of already-pushed commits ===
rem See header "rebase footgun". On divergence, flag (a) identical tree or
rem (b) shared subject. Set STAGING_ALLOW_REWRITE=1 to skip a coincidence.
if not "%STAGING_ALLOW_REWRITE%"=="1" (
    git merge-base --is-ancestor %REMOTE%/staging HEAD
    if errorlevel 1 (
        set "REWRITE=0"
        for /f %%t in ('git rev-parse "HEAD:"') do set LOCALTREE=%%t
        for /f %%t in ('git rev-parse "%REMOTE%/staging:"') do set REMOTETREE=%%t
        if "!LOCALTREE!"=="!REMOTETREE!" set "REWRITE=1"
        rem subject-collision: PowerShell intersection (findstr mishandles UTF-8 CJK).
        rem Both sides decode identically, so matching commit titles still compare equal.
        powershell -NoProfile -Command "$r=git log --format=%%s HEAD..%REMOTE%/staging; $l=git log --format=%%s %REMOTE%/staging..HEAD; @($r | Where-Object {$l -contains $_}) | Sort-Object -Unique" > "%TEMP%\gs_dup_subj.txt"
        set "SHOWDUP=0"
        for %%A in ("%TEMP%\gs_dup_subj.txt") do if %%~zA GTR 0 set "SHOWDUP=1"
        if "!SHOWDUP!"=="1" set "REWRITE=1"
        if "!REWRITE!"=="1" (
            echo.
            echo WARNING: you may have rewritten a commit already pushed to staging.
            if "!SHOWDUP!"=="1" (
                echo Same-title commits on both sides of the divergence:
                type "%TEMP%\gs_dup_subj.txt"
            )
            echo.
            echo Plain rebase will NOT replace the remote commit - it stacks a duplicate
            echo same-title commit instead. When rewriting already-pushed history, bypass
            echo this script and force-push manually:
            echo     git push %REMOTE% %MY_BRANCH% --force-with-lease
            echo     git push %REMOTE% %MY_BRANCH%:refs/heads/staging --force-with-lease
            echo.
            echo If these are genuinely different changes that share a title, re-run with:
            echo     set STAGING_ALLOW_REWRITE=1
            del "%TEMP%\gs_dup_subj.txt" >nul 2>&1
            exit /b 1
        )
        del "%TEMP%\gs_dup_subj.txt" >nul 2>&1
    )
)
echo.

echo === 3. rebase %MY_BRANCH% onto %REMOTE%/staging ===
git rebase --autostash %REMOTE%/staging
if errorlevel 1 (
    echo.
    echo Rebase stopped on a conflict. Resolve the files, then:
    echo     git add ^<files^>
    echo     git rebase --continue
    echo Then re-run git-staging.bat. To bail out: git rebase --abort
    exit /b 1
)
echo.

echo === 4. push %MY_BRANCH% ===
git push %REMOTE% %MY_BRANCH% --force-with-lease
if errorlevel 1 exit /b 1
echo.

echo === 5. FF push %MY_BRANCH% to %REMOTE%/staging ===
git push %REMOTE% %MY_BRANCH%:refs/heads/staging
if errorlevel 1 exit /b 1
echo.

echo === Done. staging is synced. The project owner runs git-main.bat to publish. ===
