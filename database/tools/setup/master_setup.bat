@echo off
REM ============================================
REM SURELIFE - MASTER SETUP SCRIPT (Windows)
REM ============================================
REM
REM This script runs all migrations, seeders, and utility scripts
REM in the correct order to set up the database after importing
REM the original database from the server.
REM
REM Usage: Double-click this file or run from command line:
REM        master_setup.bat
REM
REM Prerequisites:
REM - PHP installed and in PATH
REM - Composer installed
REM - Laravel .env configured with correct database credentials
REM - Original database already imported
REM
REM Note: TestClientSeeder is NOT included here. It's a testing tool.
REM       Run it separately if needed: php artisan db:seed --class=TestClientSeeder
REM
REM ============================================

echo.
echo ============================================
echo   SURELIFE - MASTER SETUP SCRIPT
echo ============================================
echo.

REM Check if PHP is available
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your PATH environment variable
    pause
    exit /b 1
)

REM Check if we're in the Laravel project directory
if not exist "artisan" (
    echo ERROR: artisan file not found
    echo Please run this script from the Laravel project root directory
    echo Current directory: %CD%
    pause
    exit /b 1
)

echo Step 1: Running Migrations...
echo ----------------------------------------
php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo WARNING: Migration returned non-zero exit code
)
echo.

echo Step 2: Seeding Address Data...
echo ----------------------------------------
php artisan db:seed --class=AddressSeeder --force
echo.

echo Step 3: Seeding Reference Tables...
echo ----------------------------------------
php artisan db:seed --class=ReferenceTablesSeeder --force
echo.

echo Step 4: Seeding Loan Menu Entry...
echo ----------------------------------------
php artisan db:seed --class=LoanMenuSeeder --force
echo.

echo Step 5: Merging Zip Codes...
echo ----------------------------------------
php database/tools/address/smart_merge_zips.php
if %ERRORLEVEL% neq 0 (
    echo WARNING: Zip code merge returned non-zero exit code
    echo This may be normal if the script needs database config update.
)
echo.

echo ============================================
echo   SETUP COMPLETE
echo ============================================
echo.
echo All migrations and seeders have been executed.
echo.
echo If you encountered any errors, please check:
echo 1. Database connection in .env file
echo 2. Original database was imported correctly
echo 3. PHP and Composer are properly installed
echo.
echo NOTE: TestClientSeeder was NOT run (it's a testing tool).
echo To create test accounts, run:
echo   php artisan db:seed --class=TestClientSeeder
echo.

pause
