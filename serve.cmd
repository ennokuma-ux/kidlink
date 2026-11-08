@echo off
REM Starts the KidLink dev server with a PHP binary that actually has the PDO
REM driver the configured database connection needs.
REM
REM Plain "php artisan serve" fails on this machine because "php" resolves to the
REM statically built herd-lite PHP 8.4.0, which ships mysql/sqlite only and cannot
REM load pdo_pgsql at all. This script probes the known PHP installs in order and
REM starts the server with the first one that can load the driver the app needs.
REM
REM Override the search with:  set KIDLINK_PHP=C:\path\to\php.exe
REM Pass a port as the first argument:  serve.cmd 8080

setlocal EnableExtensions EnableDelayedExpansion

set "ROOT=%~dp0"
cd /d "%ROOT%"

set "PORT=8000"
if not "%~1"=="" set "PORT=%~1"

set "REQUIRED_DRIVER="
for /f "usebackq delims=" %%D in (`php "%ROOT%artisan" env:doctor --print-driver 2^>nul`) do (
    if not defined REQUIRED_DRIVER set "REQUIRED_DRIVER=%%D"
)

if not defined REQUIRED_DRIVER (
    echo [serve] Could not determine which PDO driver the app needs.
    echo [serve] Run "php artisan env:doctor" for the full report.
    exit /b 1
)

REM PDO::getAvailableDrivers() reports bare names such as "pgsql".
set "DRIVER_NAME=%REQUIRED_DRIVER:pdo_=%"

echo [serve] Required PDO driver: %REQUIRED_DRIVER%

set "CHOSEN="

REM 1. An explicit override always wins.
if defined KIDLINK_PHP call :try "%KIDLINK_PHP%"

REM 2. Laragon installs, newest version first.
if not defined CHOSEN for /d %%D in ("C:\laragon\bin\php\php-*") do (
    if not defined CHOSEN call :try "%%~fD\php.exe"
)

REM 3. Whatever "php" resolves to on PATH, so a correct setup needs no hardcoding.
if not defined CHOSEN for /f "usebackq delims=" %%D in (`where php 2^>nul`) do (
    if not defined CHOSEN call :try "%%D"
)

REM 4. Other known locations, probed for completeness and better diagnostics.
if not defined CHOSEN call :try "C:\xampp\php\php.exe"
if not defined CHOSEN call :try "%USERPROFILE%\.config\herd-lite\bin\php.exe"

if not defined CHOSEN (
    echo.
    echo [serve] No PHP on this machine can load %REQUIRED_DRIVER%.
    echo [serve] Install a PHP build that includes it, or point KIDLINK_PHP at one:
    echo.
    echo [serve]     set KIDLINK_PHP=C:\path\to\php.exe
    echo.
    echo [serve] A statically compiled PHP cannot load shared extensions, so
    echo [serve] enabling the extension in php.ini alone will not be enough.
    exit /b 1
)

echo [serve] Using: !CHOSEN!
echo [serve] URL  : http://127.0.0.1:%PORT%/
echo.

"!CHOSEN!" "%ROOT%artisan" serve --host=127.0.0.1 --port=%PORT%

endlocal & exit /b %errorlevel%

:try
if not defined CHOSEN call :probe "%~1"
exit /b 0

:probe
if "%~1"=="" exit /b 0
if not exist "%~1" exit /b 0

"%~1" -r "exit(in_array('%DRIVER_NAME%', PDO::getAvailableDrivers(), true) ? 0 : 1);" >nul 2>&1
if errorlevel 1 exit /b 0

set "CHOSEN=%~1"
exit /b 0
