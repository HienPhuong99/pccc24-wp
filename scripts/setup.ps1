# Dựng lại toàn bộ Pccc24-WP từ đầu (idempotent): powershell -ExecutionPolicy Bypass -File scripts\setup.ps1
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
Set-Location $root
$src = 'D:\Code\PCCC24\public\uploads'
function wp { & php "$root\tools\wp-cli.phar" @args; if ($LASTEXITCODE) { throw "wp $args failed" } }

if (-not (Test-Path tools\wp-cli.phar)) {
    New-Item -ItemType Directory -Force tools | Out-Null
    Invoke-WebRequest https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -OutFile tools\wp-cli.phar
}
if (-not (Test-Path wp-includes)) { wp core download --locale=vi --skip-content }
php -r "`$m=new mysqli('127.0.0.1','root','000000'); `$m->query('CREATE DATABASE IF NOT EXISTS pccc24_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci') or exit(1);"
if (-not (Test-Path wp-config.php)) { wp config create --dbname=pccc24_wp --dbuser=root --dbpass=000000 --dbhost=127.0.0.1 --locale=vi --skip-check }

& php "$root\tools\wp-cli.phar" core is-installed
if ($LASTEXITCODE) {
    wp core install --url=http://localhost:8080 --title=PCCC24 --admin_user=admin --admin_password=pccc24@admin2026 --admin_email=admin@pccc24.local --skip-email
    wp post delete 1 2 3 --force   # nội dung mẫu của WP
}
wp rewrite structure '/%postname%'   # URL gốc không có "/" cuối
wp plugin activate pccc24-core
wp theme activate pccc24-theme

New-Item -ItemType Directory -Force wp-content\uploads\pccc24 | Out-Null
robocopy $src wp-content\uploads\pccc24 /E /XO /NFL /NDL /NJH /NJS /NP | Out-Null
if ($LASTEXITCODE -ge 8) { throw 'robocopy failed' }

wp eval-file scripts\migrate-from-ci4.php
wp eval-file scripts\verify-migration.php
