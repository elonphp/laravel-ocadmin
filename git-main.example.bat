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
rem   2. check REMOTE below matches this project's remote name
rem   3. git-main.bat is gitignored - customize freely
rem
rem WHO MAY RUN THIS: the project owner ONLY. git-staging.bat is for every
rem developer; this one is not. Reason: it replaces main with the WHOLE of
rem staging - all or nothing, you cannot publish only part of it.
rem
rem Purpose: push REMOTE/staging to REMOTE/main.
rem   Projects with a production site: main is the deploy source, so follow
rem   this with deploy-finish.sh on the server.
rem
rem Flow:
rem   1. fetch REMOTE (pre-check: REMOTE/main already equals staging -> abort)
rem   2. backup: incremental old-main merge (one merge node per publish,
rem      nothing is ever lost)
rem   3. force-push REMOTE/staging to REMOTE/main
rem
rem All refs are remote refs; your local staging/main branches are not involved.
rem
rem ------------------------------------------------------------
rem Why --force in step 3?
rem
rem   git-staging.bat may overwrite staging when the only differing commits
rem   are your own rewritten ones (amend / squash). After that, staging no
rem   longer descends from main, so a fast-forward push would fail every time
rem   an already-published commit is amended. main is meant to equal staging
rem   exactly, so it is overwritten. Safety comes from step 2: the main being
rem   replaced is always archived in old-main first (see Rollback).
rem
rem   Trade-off: main cannot have branch protection against force pushes.
rem ------------------------------------------------------------
rem
rem Before publishing:
rem   - no half-finished commits on staging
rem   - if git-staging merged someone else's commits, look at them first
rem   - you have run it locally and it works
rem
rem Rollback:
rem   one step: git push <remote> <remote>/old-main^^2:refs/heads/main --force
rem             (old-main HEAD is a merge commit; ^^2 = second parent =
rem              the previous main)
rem   further back: git log <remote>/old-main --merges --first-parent to find
rem             the target merge commit, then push its ^^2
rem ============================================================

rem This project's remote name - the ONLY line that differs between projects.
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
echo.

echo === 2. Backup: old-main incremental merge ===
rem --verify --quiet prints NOTHING when old-main is missing. Plain rev-parse
rem echoes the ref name back to stdout, which would be mistaken for a SHA.
set OLD_MAIN_SHA=
for /f %%i in ('git rev-parse --verify --quiet %REMOTE%/old-main') do set OLD_MAIN_SHA=%%i

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
    set NEW_OLD_SHA=
    for /f %%i in ('git commit-tree %MAIN_TREE% -p %OLD_MAIN_SHA% -p %MAIN_SHA% -m "backup: archive main %MAIN_SHORT% (deploy %DATE_UTC%)"') do set NEW_OLD_SHA=%%i
    rem An empty SHA would turn the push into ":refs/heads/old-main" = DELETE.
    if "!NEW_OLD_SHA!"=="" (
        echo ERROR: could not build the old-main backup commit. Nothing was published.
        exit /b 1
    )
    git push %REMOTE% !NEW_OLD_SHA!:refs/heads/old-main
    if errorlevel 1 exit /b 1
)
echo.

echo === 3. Publish %REMOTE%/staging to %REMOTE%/main ===
git push %REMOTE% %REMOTE%/staging:refs/heads/main --force
if errorlevel 1 exit /b 1

echo.
echo === Done ===
echo Projects with a production site: run ./deploy-finish.sh on the server next.
echo   (composer install / db:transition / optimize:clear - git pull does none of these)
echo Rollback: git push %REMOTE% %REMOTE%/old-main^^2:refs/heads/main --force
