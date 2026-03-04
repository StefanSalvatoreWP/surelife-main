@echo off
title MYSQL Importing Big File 
cd/
echo Please wait for a while . . .
cd xampp\mysql\bin 
mysql -u root -p slc_db <D:\slc.sql
echo /n
echo /n
echo ********************************
echo Successfully Uploaded the file. 
echo Thank you for using this program.
echo ********************************
pause
