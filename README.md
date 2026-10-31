# 🔥 PCCC24 — WordPress Migration (`pccc24.com`)

Dự án dịch ngược toàn diện website **`https://pccc24.com`** (CodeIgniter 4 + React) bằng [`rea-agents`](https://github.com/morluto/rea) và chuyển đổi 100% sang nền tảng **WordPress** với Custom Plugin (`pccc24-core`), Custom Theme (`pccc24-theme`), công cụ di trú tự động (`scripts/migrate-from-ci4.php`) và bộ kiểm thử E2E Playwright (`tests/e2e/pccc24-wp.spec.ts`).

- 🌐 **Live Demo (GitHub Pages):** **[https://hienphuong99.github.io/pccc24-wp/](https://hienphuong99.github.io/pccc24-wp/)**
- 🛠️ **WP-Admin Demo:** **[https://hienphuong99.github.io/pccc24-wp/wp-admin/](https://hienphuong99.github.io/pccc24-wp/wp-admin/)**

---

## 📦 Cấu Trúc Dự Án

- `wp-content/plugins/pccc24-core/pccc24-core.php` — Custom Post Types (`pccc_product`, `pccc_service`, `pccc_slider`, `pccc_policy`, `pccc_support`, `pccc_order`), Taxonomies (`pccc_product_cat`, `pccc_service_type`, `pccc_page_type`), Rewrite Rules giữ nguyên 100% URL gốc của `pccc24.com`, REST API `/wp-json/pccc24/v1/*`, Admin Metaboxes & Cài đặt PCCC24.
- `wp-content/themes/pccc24-theme/` — Giao diện WordPress tái tạo 100% thiết kế Desktop & Mobile của `pccc24.com`, tích hợp OwlCarousel, Lightbox, Xem nhanh sản phẩm (`previewProduct`), Giỏ hàng `localStorage` & Đặt hàng nhanh (`POST /wp-json/pccc24/v1/orders`).
- `scripts/setup.ps1` — Script dựng lại toàn bộ WordPress + Database + Migrate dữ liệu & hình ảnh từ đầu chỉ với 1 lệnh.
- `scripts/migrate-from-ci4.php` & `scripts/verify-migration.php` — Công cụ di trú và đối soát tự động **15/15 hạng mục khớp 100%** (385 sản phẩm, 40 danh mục, 233 thông số kỹ thuật, 723 ảnh, 62 tin tức, 14 dịch vụ, 12 trang tĩnh, 15 cài đặt...).
- `scripts/export-gh-pages.php` — Kết xuất bản tĩnh tương tác cho GitHub Pages (`gh-pages`).
- `tests/e2e/pccc24-wp.spec.ts` — Bộ kiểm thử tự động E2E Playwright trên cả Desktop & Mobile (**40/40 passed**).
- `docs/REA-REVERSE-ENGINEERING-REPORT.md` — Hồ sơ bằng chứng dịch ngược từ `rea analyze-javascript-application`.
- `Chat.md` — Nhật ký phối hợp tự động giữa **Argon (Gemini)** và **Opus (Claude)**.

---

## 🚀 Khởi Chạy Nhanh Trên Máy Local

```powershell
# 1. Cài đặt tự động WordPress + Migrate dữ liệu từ đầu
powershell -ExecutionPolicy Bypass -File scripts\setup.ps1

# 2. Khởi chạy server tại http://localhost:8080
.\start-server.bat

# 3. Chạy kiểm thử tự động Playwright E2E (Desktop + Mobile)
npm run test:e2e
```
