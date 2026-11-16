# Đóng gói bản production để upload lên hosting (xem docs/DEPLOY.md):
#   powershell -ExecutionPolicy Bypass -File scripts\build-release.ps1 -Domain https://pccc24.com -DbName ... -DbUser ... -DbPass ... [-DbHost localhost]
# Ra thư mục dist\: pccc24-site.zip (giải nén vào public_html), pccc24.sql (import vào DB rỗng), ADMIN-LOGIN.txt (mật khẩu admin mới).
# Không đụng DB local: domain được thay ngay trong file SQL xuất ra.
param(
    [string]$Domain = 'https://pccc24.com',
    [string]$DbName = $(if ($env:PCCC24_DB_NAME) { $env:PCCC24_DB_NAME } else { 'DOI_TEN_DATABASE' }),
    [string]$DbUser = $(if ($env:PCCC24_DB_USER) { $env:PCCC24_DB_USER } else { 'DOI_USER_DATABASE' }),
    [string]$DbPass = $(if ($env:PCCC24_DB_PASS) { $env:PCCC24_DB_PASS } else { 'DOI_MAT_KHAU_DATABASE' }),
    [string]$DbHost = $(if ($env:PCCC24_DB_HOST) { $env:PCCC24_DB_HOST } else { 'localhost' }),
    [string]$AdminEmail = 'hoangquanphat@gmail.com',
    [string]$Out = 'dist'
)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
Set-Location $root
function wp { & php "$root\tools\wp-cli.phar" @args; if ($LASTEXITCODE) { throw "wp $args failed" } }

$Domain = $Domain.TrimEnd('/')
$Out = [IO.Path]::GetFullPath((Join-Path $root $Out))
$site = "$Out\site"
if (Test-Path $Out) { Remove-Item -Recurse -Force $Out }
New-Item -ItemType Directory -Force $site | Out-Null

# 1. Mã nguồn: WP core + plugin/theme PCCC24 + uploads; bỏ công cụ dev, test, tài liệu, theme mặc định, file lộ phiên bản.
$xd = 'tools', 'node_modules', 'tests', 'docs', 'scripts', 'playwright-report', 'test-results', 'design_handoff_pccc24_ios',
      '.git', '.claude', 'wp-content\themes\twentytwentyfive', 'wp-content\upgrade', 'wp-content\cache' | ForEach-Object { "$root\$_" }
$xf = 'wp-config.php', 'wp-config-sample.php', 'readme.html', 'license.txt', 'start-server.bat', 'wp.bat', 'dong-goi-website.bat','README.md', 'CLAUDE.md',
      'Chat.md', 'V18.mp4', 'package.json', 'package-lock.json', 'playwright.config.ts', '.gitignore', '.htaccess' | ForEach-Object { "$root\$_" }
robocopy $root $site /E /NFL /NDL /NJH /NJS /NP /XD $xd $Out /XF $xf .DS_Store | Out-Null
if ($LASTEXITCODE -ge 8) { throw 'robocopy failed' }
Copy-Item scripts\htaccess-production "$site\.htaccess"

# 2. wp-config.php mới: salt mới (cookie đăng nhập local vô hiệu), chặn sửa code trong wp-admin.
$ssl = if ($Domain -like 'https:*') { 'true' } else { 'false' }
$extra = @"
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'FORCE_SSL_ADMIN', $ssl );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
"@
wp config create --path="$site" --dbname="$DbName" --dbuser="$DbUser" --dbpass="$DbPass" --dbhost="$DbHost" --dbcharset=utf8mb4 --locale=vi --skip-check --force
$cfg = [IO.File]::ReadAllText("$site\wp-config.php")
[IO.File]::WriteAllText("$site\wp-config.php", $cfg.Replace("if ( ! defined( 'ABSPATH' ) )", "$extra`nif ( ! defined( 'ABSPATH' ) )"), (New-Object Text.UTF8Encoding $false))
if (-not (Select-String -Quiet DISALLOW_FILE_EDIT "$site\wp-config.php")) { throw 'wp-config.php thiếu cấu hình production' }

# 3. Database: đổi domain (kể cả dữ liệu serialize) ngay trong file xuất. Transient chỉ là cache (feed tin WP, bộ đếm chống spam) -> bỏ.
wp transient delete --all --quiet
wp search-replace 'http://localhost:8080' $Domain --all-tables-with-prefix --precise --export="$Out\pccc24.sql" --quiet

# 4. Mật khẩu admin mới (mật khẩu dev đang công khai trong repo) + email admin thật để khôi phục mật khẩu.
$pass = (wp eval 'echo wp_generate_password(20, false);')
$newHash = (wp eval "echo wp_hash_password('$pass');")
$oldHash = (wp user get admin --field=user_pass)
$utf8 = New-Object Text.UTF8Encoding $false
$sql = [IO.File]::ReadAllText("$Out\pccc24.sql", $utf8)
if (([regex]::Matches($sql, [regex]::Escape($oldHash))).Count -ne 1) { throw 'Không tìm thấy đúng 1 hash mật khẩu admin trong SQL' }
# Như header của mysqldump: WP core dùng DEFAULT '0000-00-00 00:00:00', MySQL strict mode (NO_ZERO_DATE) sẽ từ chối CREATE TABLE.
$sql = "/*!40101 SET NAMES utf8mb4 */;`nSET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';`n`n" + $sql
$sql = $sql.Replace($oldHash, $newHash).Replace("'admin@pccc24.local'", "'$AdminEmail'")
if ($sql.Contains('localhost:8080')) { Write-Warning 'SQL vẫn còn chuỗi localhost:8080' }
[IO.File]::WriteAllText("$Out\pccc24.sql", $sql, $utf8)
"Đăng nhập: $Domain/wp-admin/`r`nTài khoản: admin`r`nMật khẩu: $pass`r`n(Đổi lại trong Hồ sơ sau lần đăng nhập đầu. Xoá file này sau khi lưu vào trình quản lý mật khẩu.)" |
    Set-Content -Encoding utf8 "$Out\ADMIN-LOGIN.txt"

# 5. Nén (tên file UTF-8, giữ cả .htaccess)
& php "$root\scripts\zip-dir.php" "$site" "$Out\pccc24-site.zip"
if ($LASTEXITCODE) { throw 'zip failed' }
if ($DbName -like 'DOI_*') { Write-Warning 'Chưa có thông tin DB hosting: sửa DB_NAME/DB_USER/DB_PASSWORD trong wp-config.php sau khi giải nén, hoặc chạy lại với -DbName -DbUser -DbPass.' }
Get-ChildItem $Out -File | ForEach-Object { '{0,-20} {1,8:N1} MB' -f $_.Name, ($_.Length / 1MB) }
