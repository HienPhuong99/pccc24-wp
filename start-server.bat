@echo off
rem Chay WordPress PCCC24 tai http://localhost:8080 (wp-admin: admin)
php -S localhost:8080 -t "%~dp0."
