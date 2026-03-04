@echo off
title MYSQL Importing Big File 
set SQL_FILE="%~dp0slc.sql"
set MYSQL_EXE="C:\xampp\mysql\bin\mysql.exe"

echo Please wait for a while . . .

if not exist %SQL_FILE% (
    echo ERROR: Could not find sql file at %SQL_FILE%
    pause
    exit /b 1
)

if not exist %MYSQL_EXE% (
    echo ERROR: Could not find mysql.exe at %MYSQL_EXE%
    pause
    exit /b 1
)

%MYSQL_EXE% -u root slc_db < %SQL_FILE%

if %ERRORLEVEL% NEQ 0 (
    echo /n
    echo ********************************
    echo ERROR: Failed to upload the file.
    echo Please check if the database 'slc_db' exists and credentials are correct.
    echo ********************************
) else (
    echo /n
    echo /n
    echo ********************************
    echo Successfully Uploaded the file. 
    echo Thank you for using this program.
    echo ********************************
)

pause

