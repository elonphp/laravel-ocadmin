@echo off
rem ============================================================
rem git-staging.example.bat -- TEMPLATE
rem
rem Setup (per developer, first time):
rem   1. copy git-staging.example.bat git-staging.bat
rem   2. open git-staging.bat, change MY_BRANCH below to your branch
rem      (edit only that set line; the check below looks for the placeholder)
rem   3. git-staging.bat is gitignored - don't commit
rem
rem Why hardcode MY_BRANCH instead of detecting HEAD?
rem   If you accidentally switch to another branch and run the script,
rem   "detect HEAD" would happily push the wrong branch to staging.
rem   Hardcoding + verifying current == MY_BRANCH catches that.
rem
rem Flow:
rem   1. Verify current branch == MY_BRANCH (uncommitted in working tree ignored)
rem   2. fetch REMOTE
rem   3. reconcile with REMOTE/staging (see "Reconcile" below)
rem   4. push MY_BRANCH (--force-with-lease)
rem   5. push MY_BRANCH to REMOTE/staging
rem
rem Commit freely on MY_BRANCH (amend included). Whether anyone else has
rem pushed is checked HERE, at push time - not before each commit.
rem
rem Why no working-tree-clean check?
rem   merge/push only touch committed content; uncommitted stays in working
rem   tree and is never pushed to staging. Merge refuses on real conflict.
rem
rem ---------- Reconcile (step 3) ----------
rem Look at the commits on staging that HEAD does not have, by author email
rem (author == your git config user.email -> yours, otherwise someone else's):
rem
rem   a) remote staging does not exist yet  -> create it
rem   b) none (staging is an ancestor)      -> plain push
rem   c) all yours                          -> you rewrote them (amend /
rem      squash / rebase). OVERWRITE staging (lease pinned to the staging
rem      commit seen at fetch time, so a push in between is rejected, never
rem      wiped).
rem   d) all someone else's                 -> try "git merge --no-edit".
rem      No conflict: merge commit is created and pushed right away.
rem      Conflict: stop. Resolve, "git add", "git commit" to finish the merge,
rem      then re-run this script (it then lands in case b).
rem   e) both yours and someone else's      -> STOP, nothing merged or pushed.
rem      Your missing commits are old copies you rewrote; merging would bring
rem      them back as duplicates. Rebuild on top of staging by hand, e.g.
rem          git rebase --onto REMOTE/staging <your-old-commit> MY_BRANCH
rem
rem "No conflict" only means git found no overlapping edits. Check what was
rem merged before deploying with git-main.bat.
rem
rem Use git-main.bat to deploy staging to production main.
rem ============================================================

set MY_BRANCH=YOUR_BRANCH_HERE

rem This project's remote name - the ONLY line that differs between projects.
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

set "ME="
for /f "delims=" %%e in ('git config user.email') do set "ME=%%e"
if "%ME%"=="" (
    echo ERROR: git config user.email is not set.
    exit /b 1
)

echo === Sync %MY_BRANCH% to remote staging ===
echo.

echo === 1. fetch %REMOTE% ===
git fetch %REMOTE%
if errorlevel 1 exit /b 1
echo.

echo === 2. reconcile with %REMOTE%/staging ===
set "STAGING_SHA="
set "FORCE_STAGING=0"

rem (a) no remote staging yet
git rev-parse --verify --quiet %REMOTE%/staging >nul 2>nul
if errorlevel 1 (
    echo remote staging does not exist yet - it will be created in step 4.
    goto :push_branch
)
for /f %%s in ('git rev-parse %REMOTE%/staging') do set "STAGING_SHA=%%s"

rem (b) staging already contained in HEAD
git merge-base --is-ancestor %REMOTE%/staging HEAD
if not errorlevel 1 (
    echo staging is already contained in %MY_BRANCH% - nothing to merge.
    goto :push_branch
)

rem Count missing commits by author: yours vs someone else's
set "N_MINE=0"
set "N_OTHERS=0"
for /f "delims=" %%a in ('git log --format^=%%ae HEAD..%REMOTE%/staging') do (
    if /i "%%a"=="%ME%" (set /a N_MINE+=1) else (set /a N_OTHERS+=1)
)

rem (c) all yours -> overwrite
if "!N_OTHERS!"=="0" (
    echo Only your own commits differ ^(rewritten locally^). These on staging
    echo will be overwritten:
    git log --format="    %%h %%s" HEAD..%REMOTE%/staging
    set "FORCE_STAGING=1"
    goto :push_branch
)

echo staging has commits you do not have, some by someone else:
git log --format="    %%h %%an  %%s" HEAD..%REMOTE%/staging
echo.

rem (e) mixed -> stop
if not "!N_MINE!"=="0" (
    echo STOP: staging also has !N_MINE! old commit^(s^) of yours that you rewrote.
    echo Merging would bring them back as duplicates. Nothing was merged or pushed.
    echo Rebuild on top of staging by hand, e.g.
    echo     git rebase --onto %REMOTE%/staging ^<your-old-commit^> %MY_BRANCH%
    exit /b 1
)

rem (d) someone else's only -> try merge
echo Trying automatic merge...
git merge --no-edit %REMOTE%/staging
if errorlevel 1 (
    echo.
    echo STOP: merge conflict. Nothing was pushed. Resolve the files, then:
    echo     git add ^<files^>
    echo     git commit
    echo and re-run git-staging.bat. To give up: git merge --abort
    exit /b 1
)
echo Merged without conflict.

:push_branch
echo.

echo === 3. push %MY_BRANCH% ===
git push %REMOTE% %MY_BRANCH% --force-with-lease
if errorlevel 1 exit /b 1
echo.

if "%FORCE_STAGING%"=="1" goto :push_staging_forced

echo === 4. push %MY_BRANCH% to %REMOTE%/staging ===
git push %REMOTE% %MY_BRANCH%:refs/heads/staging
if errorlevel 1 (
    echo staging changed since the fetch ^(someone just pushed^). Re-run git-staging.bat.
    exit /b 1
)
goto :done

:push_staging_forced
echo === 4. push %MY_BRANCH% to %REMOTE%/staging ^(overwrite^) ===
git push %REMOTE% %MY_BRANCH%:refs/heads/staging --force-with-lease=refs/heads/staging:%STAGING_SHA%
if errorlevel 1 (
    echo staging changed since the fetch ^(someone just pushed^). Re-run git-staging.bat.
    exit /b 1
)

:done
echo.
echo === Done. staging is synced. Run git-main.bat to deploy to production. ===
