@echo off
rem ============================================================
rem git-main.example.bat -- TEMPLATE
rem
rem English-only on purpose: a .bat with CJK comments must be saved as
rem Big5/CP950 or cmd.exe shows mojibake; keeping this file pure ASCII
rem lets it stay UTF-8 and sidesteps the encoding trap entirely.
rem
rem Setup:
rem   1. copy git-main.example.bat git-main.bat
rem   2. check REMOTE matches this project's remote name
rem   3. git-main.bat is gitignored - customize freely
rem
rem WHO MAY RUN THIS: the project owner ONLY. git-staging.bat is for every
rem developer; this one is not. Reason: it replaces main with the WHOLE of
rem staging (see "all or nothing" below).
rem
rem Purpose: push REMOTE/staging to main.
rem   Projects with a production site: main is the deploy source, so follow
rem   this with deploy-finish.sh on the server.
rem   The ocadmin template itself has no deployment target - main is just the
rem   public release branch, so this script is the last step.
rem
rem Flow:
rem   1. fetch REMOTE (pre-check: REMOTE/main already equals staging -> abort)
rem   2. backup: incremental old-main merge (one merge node per publish,
rem      nothing is ever lost)
rem   3. push REMOTE/staging to REMOTE/main
rem
rem All refs are remote refs; your local staging branch is not involved.
rem
rem ------------------------------------------------------------
rem Why is there no --force in step 3?
rem
rem   The normal path is already a fast-forward: after each publish
rem   main == staging, and staging only grows on top of it (git-staging step 5
rem   is an FF push), so staging is always a descendant of main.
rem
rem   Benefit of not forcing: main can keep branch protection enabled against
rem   force pushes. Adding --force would mean permanently leaving main
rem   unprotected just for a case that almost never happens.
rem
rem   When is force genuinely needed? Only after main has been rolled back to
rem   an older commit (see Rollback). staging is then no longer a descendant
rem   of main and this script stops with a clear message - that is the
rem   intended warning, not a bug. Resolve it by pushing manually once:
rem       git push <remote> <remote>/staging:refs/heads/main --force
rem   (if branch protection is on, allow force push temporarily first)
rem ------------------------------------------------------------
rem
rem Before publishing:
rem   - no half-finished commits on staging (see git-staging.bat "CORE RULE")
rem   - you have run it locally and it works
rem
rem Rollback:
rem   one step: git push <remote> <remote>/old-main^^2:refs/heads/main --force
rem             (old-main HEAD is a merge commit; ^^2 = second parent =
rem              the previous main)
rem   further back: git log <remote>/old-main --merges --first-parent to find
rem             the target merge commit, then push its ^^2
rem   After a rollback the next run of this script fails as non-fast-forward,
rem   as described above. That is expected.
rem ============================================================

rem This project's remote name. Derivative projects using another name
rem (e.g. origin) should change this.
set REMOTE=elonphp

cd /d %~dp0
rem Delayed expansion is required: NEW_OLD_SHA is set and used inside the same
rem parenthesised block in step 2, and %VAR% there would expand at parse time.
setlocal enabledelayedexpansion

echo === 1. fetch %REMOTE% ===
git fetch %REMOTE%
if errorlevel 1 exit /b 1

for /f %%i in ('git rev-parse %REMOTE%/main') do set MAIN_SHA=%%i
for /f %%i in ('git rev-parse %REMOTE%/staging') do set STAGING_SHA=%%i

if "%MAIN_SHA%"=="%STAGING_SHA%" (
    echo.
    echo INFO: main already equals staging, no new commits to publish. Aborted.
    exit /b 0
)

rem Pre-check: staging must descend from main, otherwise the FF push in step 3
rem fails. Explaining it here beats a bare non-fast-forward error from git.
git merge-base --is-ancestor %MAIN_SHA% %STAGING_SHA%
if errorlevel 1 (
    echo.
    echo ERROR: staging is not a descendant of main - cannot fast-forward.
    echo   Common cause: main was rolled back to an older commit.
    echo   Once you are sure staging should overwrite main, push manually:
    echo       git push %REMOTE% %REMOTE%/staging:refs/heads/main --force
    echo   ^(if main has branch protection, allow force push temporarily first^)
    exit /b 1
)

echo.
echo === 2. Backup: old-main incremental merge ===
set OLD_MAIN_SHA=
for /f %%i in ('git rev-parse %REMOTE%/old-main 2^>nul') do set OLD_MAIN_SHA=%%i

for /f %%i in ('powershell -NoProfile -Command "(Get-Date).ToUniversalTime().ToString(\"yyyyMMddTHHmmssZ\")"') do set DATE_UTC=%%i
for /f %%i in ('git rev-parse --short %REMOTE%/main') do set MAIN_SHORT=%%i
rem "<rev>:" resolves to that commit's tree - same as <rev>^{tree} but without
rem a caret, which cmd would mangle inside a for /f command string.
for /f %%i in ('git rev-parse %REMOTE%/main:') do set MAIN_TREE=%%i

if "%OLD_MAIN_SHA%"=="" (
    echo INFO: no remote old-main yet, bootstrapping from %REMOTE%/main.
    git push %REMOTE% %REMOTE%/main:refs/heads/old-main
    if errorlevel 1 exit /b 1
) else (
    for /f %%i in ('git commit-tree %MAIN_TREE% -p %OLD_MAIN_SHA% -p %MAIN_SHA% -m "backup: archive main %MAIN_SHORT% (deploy %DATE_UTC%)"') do set NEW_OLD_SHA=%%i
    git push %REMOTE% !NEW_OLD_SHA!:refs/heads/old-main
    if errorlevel 1 exit /b 1
)

echo.
echo === 3. Publish %REMOTE%/staging to %REMOTE%/main ===
git push %REMOTE% %REMOTE%/staging:refs/heads/main
if errorlevel 1 exit /b 1

echo.
echo === Done ===
echo Projects with a production site: run ./deploy-finish.sh on the server next.
echo   (composer install / db:transition / optimize:clear - git pull does none of these)
echo Rollback: git push %REMOTE% %REMOTE%/old-main^^2:refs/heads/main --force
