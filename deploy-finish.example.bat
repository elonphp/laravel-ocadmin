@echo off
rem ============================================================
rem deploy-finish.example.bat -- TEMPLATE (Windows)
rem
rem Setup (per developer, first time):
rem   1. copy deploy-finish.example.bat deploy-finish.bat
rem   2. deploy-finish.bat is gitignored - customize freely.
rem
rem English-only on purpose: a .bat with CJK comments must be saved as
rem Big5/CP950 or cmd.exe shows mojibake; keeping this file pure ASCII
rem lets it stay UTF-8 and sidesteps the encoding trap entirely.
rem
rem Purpose: post-deploy finishing on Windows (counterpart of
rem deploy-finish.sh). The ocadmin template itself has no deployment
rem target - this is a template for derivative projects to copy.
rem Typical flow: something pulls the code on the target machine; that
rem pull does NOT do the steps below, so run this right after it.
rem
rem Idempotent (safe to run every deploy, no need to pick steps):
rem   - composer install : skips when composer.lock is unchanged
rem   - db:transition    : no-op when there are no pending transitions
rem   - optimize:clear / optimize : always safe
rem
rem This project calls PHP and Composer through the php.bat / composer.bat
rem wrappers in the repo root (multi-version PHP environment). If your
rem environment uses versioned names instead (php85.bat and friends),
rem adjust the calls below.
rem
rem deploy-finish.sh has one extra step this .bat does not need: step 5
rem fixes file ownership (chown) after running as root on a Linux panel
rem host. That is Linux-only and is skipped automatically on Windows.
rem
rem ------------------------------------------------------------
rem PREREQUISITE: make sure .env is up to date BEFORE running this.
rem
rem Step 4 (optimize) writes bootstrap/cache/config.php. Once that file
rem exists, Laravel does NOT read .env at all. So if the order ends up
rem being "deploy first (cache built) -> edit .env after", the new values
rem never take effect -- and it fails SILENTLY, falling back to the
rem defaults in config/*.php with no error at all.
rem
rem Real case: .env said MAIL_MAILER=smtp, but a stale cache fell back to
rem the default log driver. Sending raised no exception, the UI reported
rem "sent successfully", yet the mail was only written into
rem storage/logs/laravel.log and never actually sent. Telltale sign in the
rem log: a From: Laravel ^<hello@example.com^> line (the framework default).
rem
rem If you touch .env after a deploy, clear the cache and verify:
rem   php.bat artisan optimize:clear
rem   php.bat artisan tinker --execute="echo config('mail.default');"
rem ------------------------------------------------------------
rem ============================================================

cd /d %~dp0

echo === 1. composer install (skips if composer.lock unchanged) ===
call composer.bat install --no-dev --optimize-autoloader
if errorlevel 1 exit /b 1

echo.
echo === 2. db:transition (pending schema / data changes) ===
call php.bat artisan db:transition
if errorlevel 1 exit /b 1

echo.
echo === 3. optimize:clear (apply new route / config / view / lang) ===
call php.bat artisan optimize:clear
if errorlevel 1 exit /b 1

echo.
echo === 4. optimize (re-cache; optional but recommended) ===
call php.bat artisan optimize

echo.
echo === Deploy finishing complete ===
