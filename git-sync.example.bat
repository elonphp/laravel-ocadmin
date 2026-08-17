@echo off
rem ============================================================
rem git-sync.example.bat -- TEMPLATE
rem
rem Purpose:
rem   Run before you start working. Rebases your branch on top of remote
rem   staging (which already contains your teammates' integrated work) so new
rem   work stacks on the latest state. PULL ONLY - never pushes.
rem   To publish, use git-staging.bat: it runs the same rebase step and then
rem   pushes your branch back to staging.
rem
rem English-only on purpose: a .bat with CJK comments must be saved as
rem Big5/CP950 or cmd.exe shows mojibake; keeping this file pure ASCII
rem lets it stay UTF-8 and sidesteps the encoding trap entirely.
rem
rem Setup (per developer, first time):
rem   1. copy git-sync.example.bat git-sync.bat
rem   2. open git-sync.bat, change MY_BRANCH below to your branch
rem   3. check REMOTE matches this project's remote name
rem   4. git-sync.bat is gitignored - don't commit
rem
rem Flow:
rem   1. Verify current branch == MY_BRANCH
rem   2. fetch REMOTE
rem   3. rebase --autostash REMOTE/staging
rem
rem Why rebase instead of merge?
rem   Rebase replays only your commits on top of staging: history stays
rem   linear, no merge commits pile up, stale base commits are shed.
rem   Personal branches (one owner each) are safe to rebase.
rem
rem Why --autostash?
rem   It sets uncommitted work aside before the rebase and restores it after,
rem   so you can sync mid-edit. On a real conflict git still stops for a
rem   manual fix - nothing is silently discarded.
rem ============================================================

set MY_BRANCH=YOUR_BRANCH_HERE

rem This project's remote name. Derivative projects using another name
rem (e.g. origin) should change this.
set REMOTE=elonphp

cd /d %~dp0

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

echo === Sync %MY_BRANCH% with remote staging (rebase) ===
echo.

echo === 1. fetch %REMOTE% ===
git fetch %REMOTE%
if errorlevel 1 exit /b 1
echo.

echo === 2. rebase %MY_BRANCH% onto %REMOTE%/staging ===
git rebase --autostash %REMOTE%/staging
if errorlevel 1 (
    echo.
    echo Rebase stopped on a conflict. Resolve the files, then:
    echo     git add ^<files^>
    echo     git rebase --continue
    echo To bail out: git rebase --abort
    exit /b 1
)
echo.

echo === Done. %MY_BRANCH% is on top of the latest staging. Publish with git-staging.bat ===
