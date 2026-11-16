@echo off
rem Bam dup de tao bo file dua website len hosting (xem docs/DEPLOY.md). Ket qua o thu muc dist\
cd /d "%~dp0"
echo Nhap 3 thong tin kho du lieu (database) da tao tren trang quan ly hosting.
echo.
set /p PCCC24_DB_NAME=Ten database:
set /p PCCC24_DB_USER=Ten nguoi dung database:
set /p PCCC24_DB_PASS=Mat khau database:
echo.
echo Dang dong goi, mat khoang 2-5 phut...
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\build-release.ps1
if errorlevel 1 (echo. & echo LOI: chup man hinh nay gui Claude. & pause & exit /b 1)
explorer dist
echo.
echo XONG. Thu muc dist vua mo ra co 3 file: pccc24-site.zip, pccc24.sql, ADMIN-LOGIN.txt
pause
