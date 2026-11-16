# Deploy PCCC24 lên hosting thật

Yêu cầu hosting: PHP ≥ 8.1 (có `mysqli`, `zip`, `gd` hoặc `imagick`), MySQL ≥ 5.7 / MariaDB ≥ 10.4, Apache hoặc LiteSpeed (đọc `.htaccess`), SSL.

## 1. Đóng gói (máy local)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\build-release.ps1 -Domain https://pccc24.com -DbName <db> -DbUser <user> -DbPass <pass> [-DbHost localhost]
```

Hoặc bấm đúp `dong-goi-website.bat` ở thư mục gốc: hỏi 3 thông tin DB rồi chạy lệnh trên (truyền qua biến môi trường `PCCC24_DB_*`, mật khẩu có `&` `%` `!` vẫn đúng).

Ra thư mục `dist\` (không commit):

| File | Dùng để |
|---|---|
| `pccc24-site.zip` | Giải nén vào thư mục gốc web (`public_html`). Đã có `.htaccess` production + `wp-config.php` mới (salt mới, `DISALLOW_FILE_EDIT`, `FORCE_SSL_ADMIN`). |
| `pccc24.sql` | Import vào database **rỗng**. Domain đã đổi từ `localhost:8080`; mật khẩu admin đã đổi. |
| `ADMIN-LOGIN.txt` | Mật khẩu admin mới (sinh ngẫu nhiên mỗi lần build). Lưu vào trình quản lý mật khẩu rồi xoá file. |

Không truyền `-DbName/-DbUser/-DbPass` thì `wp-config.php` để chữ `DOI_...` — sửa tay sau khi giải nén.

## 2. Lên hosting (cPanel hoặc tương tự)

1. **Sao lưu site CI4 cũ** (file + DB) trước khi ghi đè.
2. Tạo database + user MySQL, cấp toàn quyền user trên database đó.
3. Upload `pccc24-site.zip` vào `public_html` → *Extract* (cần giữ file ẩn `.htaccess`).
4. phpMyAdmin → chọn database → *Import* `pccc24.sql` (hoặc `mysql -u user -p db < pccc24.sql`).
5. Bật SSL (AutoSSL / Let's Encrypt) cho `pccc24.com` và `www.pccc24.com`.
6. Vào `https://pccc24.com/wp-admin/` bằng `ADMIN-LOGIN.txt` → *Hồ sơ* đổi mật khẩu riêng, kiểm tra *Cài đặt → PCCC24* (email nhận báo giá).
7. *Cài đặt → Đường dẫn tĩnh* → bấm *Lưu* một lần (làm mới rewrite rules trên server mới).

## 3. Kiểm tra sau deploy

```bash
for p in / /san-pham /dich-vu /tin-tuc /contacts /pages/gioi-thieu /wp-json/pccc24/v1/categories; do curl -s -o /dev/null -w "%{http_code} $p\n" https://pccc24.com$p; done   # tất cả 200
for p in /xmlrpc.php /wp-config.php /readme.html /wp-json/wp/v2/users; do curl -s -o /dev/null -w "%{http_code} $p\n" https://pccc24.com$p; done   # 403/404
```

Kiểm tra giao diện bằng Playwright (chỉ đọc, không tạo dữ liệu):

```bash
PCCC24_BASE_URL=https://pccc24.com npx playwright test tests/e2e/ui-audit.spec.ts
```

Không chạy toàn bộ `npm run test:e2e` lên site thật: bộ test tạo/xoá sản phẩm, gửi yêu cầu báo giá (gửi mail thật) và reset bộ đếm bằng wp-cli trên DB local.

## Bảo mật đã có sẵn

- `pccc24-core`: chặn XML-RPC, ẩn username (REST `/wp/v2/users`, sitemap users, `?author=N`), khoá đăng nhập 15 phút sau 5 lần sai/IP, header `nosniff` / `X-Frame-Options` / `Referrer-Policy` / `Permissions-Policy` / HSTS (khi HTTPS), giới hạn 5 yêu cầu báo giá/giờ/IP + honeypot.
- `.htaccess`: ép HTTPS, chặn `wp-config.php` / `xmlrpc.php` / `readme.html` / file ẩn / `.sql` `.log` `.bak`, cấm chạy PHP trong `wp-content/uploads`, tắt liệt kê thư mục.
- Nếu site đứng sau Cloudflare/reverse proxy: khoá đăng nhập và giới hạn báo giá đang tính theo `REMOTE_ADDR` (IP proxy) → cần đọc IP thật từ header của proxy trước khi bật proxy.
- Nginx: không đọc `.htaccess`, phải chuyển các rule ở `scripts/htaccess-production` sang cấu hình server.
