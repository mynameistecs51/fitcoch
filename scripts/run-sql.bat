@echo off
REM รัน SQL ผ่าน MySQL client ของ XAMPP (ไม่ใช่ PowerShell โดยตรง)
REM ใช้: scripts\run-sql.bat database\fixes\001_fix_cohort_name_encoding.sql

set MYSQL=D:\xamp\mysql\bin\mysql.exe
if not exist "%MYSQL%" set MYSQL=C:\xampp\mysql\bin\mysql.exe

if "%~1"=="" (
  echo Usage: scripts\run-sql.bat path\to\file.sql
  exit /b 1
)

"%MYSQL%" -u root --default-character-set=utf8mb4 fitcoch < "%~1"
echo Done.
