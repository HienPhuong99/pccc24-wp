# Handoff: PCCC24 – Trang chủ phong cách iOS (giữ màu HQP)

## Tổng quan
Thiết kế lại **trang chủ** website PCCC24 (Công ty TNHH MTV Hoàng Quân Phát) theo phong cách iOS: thẻ bo góc lớn, danh sách nhóm (grouped list), thanh điều hướng trong mờ, nút dạng viên thuốc, segmented control, bottom sheet. **Giữ nguyên bảng màu thương hiệu HQP** từ theme hiện tại.

- Repo đích: `hienphuong99/pccc24-wp` (branch `main`)
- Theme đích: `wp-content/themes/pccc24-theme/`
- File CSS hiện tại: `wp-content/themes/pccc24-theme/assets/css/site.css` (biến `:root` ở đầu file)

## Về file thiết kế
`PCCC24 iOS.dc.html` trong thư mục này là **bản mẫu thiết kế bằng HTML** (prototype), KHÔNG phải code production để copy nguyên. File dùng runtime riêng (`support.js`, cú pháp `{{ }}`, `<sc-for>`, `<sc-if>`) và style inline — chỉ để tham khảo giao diện và hành vi.

**Nhiệm vụ:** dựng lại giao diện này trong theme WordPress hiện có (PHP template + `site.css` + JS thuần), dùng class CSS và biến `:root` của theme, dữ liệu lấy động từ WordPress (danh mục, sản phẩm, bài viết, slider) thay cho dữ liệu cứng trong prototype.

Mở `PCCC24 iOS.dc.html` trực tiếp trong trình duyệt (cùng thư mục với `support.js`) để xem bản chạy.

## Mức độ hoàn thiện
**High-fidelity.** Màu, cỡ chữ, bo góc, khoảng cách, đổ bóng là giá trị cuối cùng — dựng lại đúng pixel.

> Lưu ý: trong footer, một số dòng của khối thông tin công ty có `width: 59Xpx; height: XXpx` cố định (do chỉnh tay trong editor). **Bỏ các width/height cố định này**, để dòng co giãn theo nội dung (width 100%, height auto).

---

## Design Tokens

### Màu (giữ nguyên từ site.css)
| Token | Hex | Dùng cho |
|---|---|---|
| `--red` | `#c8201a` | Nút "Báo giá", link "Xem tất cả", giá "Liên hệ", nút "Chỉ đường", nút gửi form |
| `--red-dark` | `#9e1712` | Hover của nút đỏ |
| `--ink` | `#1d2329` | Chữ chính, thẻ tin nổi bật (nền tối), ô tháng đã bấm |
| `--ink-soft` | `#4a545e` | Chữ phụ, nhãn nhóm |
| `--line` | `#d5d9d6` | (giữ) |
| `--concrete` | `#eef0ee` | **Nền trang**, nền bottom sheet, nền ô icon |
| `--paper` | `#ffffff` | Nền thẻ/danh sách |
| `--exit` | `#0b7a4b` | Số điện thoại, nút "Gọi báo giá" |
| `--exit-dark` | `#075c38` | Hover nút xanh |
| `--tag` | `#f3c623` | Chữ ô tháng đã bấm, nhãn "MỚI NHẤT" |
| Zalo | `#0068ff` | Nút Zalo |

Màu bổ sung:
- Nền thẻ "Liên hệ báo giá": `#F2F1EB`
- Chấm trạng thái thẻ báo giá: nền `#fff`, viền `box-shadow: 0 0 0 3px #5FAD8E`
- Nền ô ảnh sản phẩm: `#f7f8f7`
- Fill iOS (ô tìm kiếm, nền segmented control): `rgba(118,118,128,.12)`
- Đường kẻ phân cách trong danh sách: `1px solid rgba(29,35,41,.08)`, thụt trái 16px (kiểu iOS)
- Mũi tên ›: `#b4bab6`

### Font
```
font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Be Vietnam Pro", system-ui, sans-serif;
-webkit-font-smoothing: antialiased;
```
Load Be Vietnam Pro 400/500/600/700 từ Google Fonts (fallback cho Windows/Android). **Không dùng Barlow Condensed nữa** cho trang này.

| Vai trò | Size | Weight | Khác |
|---|---|---|---|
| Large title (H1) | `clamp(30px,4vw,40px)` | 700 | line-height 1.1, letter-spacing -.02em |
| Tiêu đề mục (H2) | 24px | 700 | letter-spacing -.015em |
| Tiêu đề thẻ báo giá | 26px | 700 | letter-spacing -.01em, lh 1.1 |
| Phụ đề hero | 17px | 400 | màu ink-soft |
| Body / dòng danh sách | 15px | 400–500 | |
| Tên sản phẩm | 14px | 500 | lh 1.38, clamp 2 dòng |
| Nút / chip | 13–15px | 500–600 | |
| Nhãn nhóm (uppercase) | 13px | 600 | letter-spacing .03em, ink-soft |
| Caption / ngày | 12–13px | 400 | ink-soft |

### Bo góc
- 999px: nút viên thuốc, chip, chấm slider
- 22px: banner, thẻ báo giá, thẻ tin
- 18px: thẻ sản phẩm, thẻ dịch vụ, khối cam kết, khối footer, bản đồ
- 14px: danh sách nhóm nhỏ, nút lớn trong thẻ
- 12px: ô ảnh sản phẩm
- 10px: ô tìm kiếm, nền segmented, ô icon, ảnh thumbnail
- 8px: nút trong segmented
- 24px: bottom sheet

### Đổ bóng
- Thẻ thường: `0 1px 2px rgba(29,35,41,.05–.06)`
- Banner: `0 1px 2px rgba(29,35,41,.06), 0 8px 24px rgba(29,35,41,.06)`
- Thẻ báo giá: `0 8px 24px rgba(29,35,41,.08)`
- Segment đang chọn: `0 3px 8px rgba(0,0,0,.12), 0 1px 1px rgba(0,0,0,.04)`
- Thanh liên hệ nổi: `0 10px 30px rgba(29,35,41,.18), 0 0 0 1px rgba(29,35,41,.06)`

### Hiệu ứng kính mờ (iOS material)
```
background: rgba(255,255,255,.72–.82);
backdrop-filter: saturate(180%) blur(20px);
-webkit-backdrop-filter: saturate(180%) blur(20px);
```
Dùng cho: header, thanh liên hệ nổi, nhãn trên bản đồ.

### Khoảng cách
- Container: `max-width: 1240px; padding: 0 20px; margin: 0 auto`
- Khoảng giữa các section: 44px (footer cách main 56px)
- Gap lưới: 14–16px
- Padding thẻ: 14px (sản phẩm), 18–22px (thẻ lớn), 24px (thẻ tin)
- Padding dòng danh sách: 11–12px dọc, 16px ngang

---

## Bố cục trang (từ trên xuống)

### 1. Header (sticky, kính mờ)
- `position: sticky; top: 0; z-index: 20`, viền dưới `1px solid rgba(29,35,41,.08)`.
- Một hàng flex, `gap: 16px`, padding `10px 20px`, **không wrap**:
  1. Logo `https://pccc24.com/uploads/template-2-2026-03-24t1404074301774335857.png`, cao 40px, `flex: none`.
  2. Nav: các link viên thuốc (padding `7px 12px`, 14px/500, ink-soft). Mục đang active: nền `rgba(29,35,41,.06)`, chữ ink. Nav `overflow-x: auto`, ẩn scrollbar, `white-space: nowrap`, `min-width: 0` để cuộn ngang khi hẹp. Mục: Sản phẩm, Thi công, Bảo trì, Dự án, Tin tức, Liên hệ.
  3. Ô tìm kiếm kiểu iOS: `flex: 1 1 160px; min-width: 120px`, nền fill iOS, bo 10px, padding `8px 12px`, icon kính lúp 16px, placeholder "Tìm thiết bị", 15px.
  4. Nút "Báo giá": nền red, chữ trắng 14px/600, padding `9px 16px`, bo 999px, `flex: none`. Active: `scale(.97)`. Bấm → mở bottom sheet báo giá.
- Menu danh mục con (dropdown) của site cũ: giữ chức năng, style lại thành popover bo 14px nền trắng + bóng.

### 2. Tiêu đề trang (Large Title)
Padding `28px 0 16px`, flex column gap 6px:
- Dòng nhỏ: "CÔNG TY TNHH MTV HOÀNG QUÂN PHÁT" — 13px/600, ink-soft, letter-spacing .02em
- H1: "Thiết bị phòng cháy chữa cháy"
- Phụ đề: "Tư vấn thiết kế thi công lắp đặt hệ thống phòng cháy chữa cháy" — 17px, ink-soft

### 3. Hàng chip danh mục
Flex, gap 8px, cuộn ngang (ẩn scrollbar), padding `4px 0 20px`. Mỗi chip: nền trắng, bo 999px, padding `8px 14px`, 14px/500, bóng nhẹ; số lượng con (nếu có) 12px ink-soft bên phải. Lấy từ taxonomy danh mục sản phẩm cấp 1 (Bình chữa cháy 8, Thiết bị báo cháy 5, Đèn thoát hiểm 3, Máy bơm chữa cháy 3, Thiết bị PCCC 8, Tủ kệ PCCC, Dụng cụ bảo hộ 3, Thiết bị PCCC khác, Kim chống sét, Biển báo an toàn).

### 4. Hero: Banner + Thẻ "Liên hệ báo giá"
Wrapper: `display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch`.
- **Banner**: `flex: 2 1 520px; min-height: 260px; min-width: 0`, bo 22px, overflow hidden, nền trắng. **Cao bằng thẻ báo giá** (stretch). Track slide `position: absolute; inset: 0; display: flex`, mỗi slide `flex: 0 0 100%; height: 100%`, ảnh `object-fit: cover`. Chuyển slide bằng `transform: translateX(-N*100%)`, `transition: transform 450ms cubic-bezier(.23,1,.32,1)`. Tự chạy mỗi 5s.
  - Chấm điều hướng: viên thuốc kính mờ ở giữa đáy (`bottom: 12px`), nền `rgba(29,35,41,.28)` + blur 12px, padding `6px 8px`, gap 6px. Chấm cao 7px; chấm active rộng 22px nền trắng, chấm thường 7px nền `rgba(255,255,255,.55)`; transition 250ms.
  - Ảnh: `template-2-2026-02-11t1636510921770802731.png`, `1714876176_27656c43b29c41f1e677.jpg`, `template-2-2026-03-13t1519031021773389956.png` (đều ở `https://pccc24.com/uploads/`). Link mỗi slide giữ như site cũ.
- **Thẻ báo giá**: `flex: 1 1 300px`, nền `#F2F1EB`, bo 22px, padding 22px, flex column gap 12px.
  - Hàng đầu: "PCCC Hoàng Quân Phát" (12px/700, letter-spacing .06em) + chấm tròn 12px viền xanh `#5FAD8E` bên phải.
  - "Liên hệ báo giá" (26px/700)
  - "Gọi hoặc nhắn Zalo, kỹ thuật viên tư vấn đúng thiết bị và báo giá trong ngày." (15px, lh 1.45)
  - Danh sách nhóm nền `rgba(255,255,255,.55)`, bo 14px: dòng "Vật tư PCCC" → **0982765114**; dòng "Thi công và bảo trì PCCC" → **02866566422** (số in đậm màu exit, là link `tel:`). Padding dòng `11px 14px`, 15px. Phân cách 1px `rgba(29,35,41,.1)` thụt trái 14px.
  - Nút "Nhắn Zalo": full width, nền `#0068ff`, chữ trắng 15px/600, padding 12px, bo 14px → `https://zalo.me/0982765114`.
  - Lưới 12 tháng (T1–T12): `grid-template-columns: repeat(6,1fr); gap: 4px`, viền trên `1px dashed rgba(29,35,41,.35)`, padding-top 10px. Ô: 11px/600, bo 6px, padding `3px 0`. Tháng đã bấm (T1–T10 mặc định): nền ink, chữ tag-yellow. Chưa bấm: nền `rgba(255,255,255,.45)`, chữ ink. (Có thể cho admin cấu hình số tháng đã bấm, hoặc bấm theo tháng hiện tại.)
- Khi màn hình hẹp: thẻ báo giá xuống dòng và chiếm full width (không được để trống nửa bên phải).

### 5. Khối 4 cam kết
`margin-top: 20px`, nền trắng, bo 18px, `display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%,240px),1fr))`. Mỗi mục: flex gap 12px, padding `14px 16px`; ô icon 40×40 bo 10px nền concrete chứa ảnh 28×28; tiêu đề 15px/600; mô tả 13px ink-soft lh 1.35.

| Icon (uploads/) | Tiêu đề | Mô tả |
|---|---|---|
| `1706704462_9744f95e1ab7371b0213.webp` | Giao hàng nhanh | Giao trong ngày hoặc dưới 24h |
| `1706704520_250f26fe295b373b7db9.webp` | Tư vấn 24/7 | Qua điện thoại và Zalo, cả ngoài giờ |
| `1706704603_79880ff942f3cb6931c8.webp` | Hàng chính hãng | Có tem kiểm định, đổi trả theo yêu cầu |
| `1706704665_3c8e2320b6a7d70671e1.webp` | Đúng quy chuẩn | Thi công, bảo trì theo quy định hiện hành |

### 6. "Bạn đang cần" (dịch vụ)
H2 + lưới `repeat(auto-fill, minmax(min(100%,190px),1fr))`, gap 14px. Mỗi thẻ: tỉ lệ 4:5, bo 18px, ảnh `object-fit: cover`, nhãn đè ở đáy với gradient `linear-gradient(transparent, rgba(29,35,41,.85))`, padding `28px 14px 14px`, chữ trắng 16px/600. Hover: `translateY(-2px)`.
Ảnh (uploads/, đuôi .png): `template-2-2026-03-05t1131441071772685130` Thi công PCCC · `template-2-2026-03-05t1147436151772686095` Bảo trì sửa chữa · `template-2-2026-03-04t0952325511772592769` Bình Chữa Cháy Giá Sỉ · `template-2-2026-03-04t1000280961772593394` Thiết bị báo cháy · `template-2-2026-03-05t1040506661772682056` Thiết bị chữa cháy. Link giữ như site cũ.

### 7. "Sản phẩm nổi bật" (gộp 3 khối sản phẩm cũ)
- Header: H2 "Sản phẩm nổi bật" + link đỏ "Xem tất cả {N} sản phẩm ›" (N theo tab đang chọn, link tới danh mục tương ứng).
- **Segmented control**: `inline-flex`, nền fill iOS, bo 10px, padding 2px, cuộn ngang nếu hẹp. Nút: 14px, padding `7px 16px`, bo 8px; đang chọn = nền trắng + bóng segment + weight 600; không chọn = trong suốt, weight 500; transition 200ms. Tab: **Bình bột ABC** (24) · **Bình CO2** (17) · **Vòi chữa cháy** (7). Đổi tab → đổi 5 sản phẩm hiển thị (có thể render sẵn 3 nhóm và ẩn/hiện bằng JS).
- Lưới sản phẩm: `repeat(auto-fill, minmax(min(100%,200px),1fr))`, gap 14px. Thẻ: nền trắng, bo 18px, padding 14px, flex column gap 10px. Ô ảnh vuông bo 12px nền `#f7f8f7`, ảnh `object-fit: contain`. Tên 14px/500 clamp 2 dòng (min-height 2.76em). Đáy: "Liên hệ" (14px/600 đỏ) + nút "Xem nhanh" (viên thuốc, nền `rgba(200,32,26,.1)`, chữ đỏ 13px/600, padding `6px 12px`). Cả thẻ bấm được (link sản phẩm), nút Xem nhanh nằm trên.
- Biến thể dạng danh sách (tùy chọn): danh sách nhóm nền trắng bo 18px, mỗi dòng ảnh 60×60 bo 10px + tên + "Liên hệ" + nút Xem nhanh.
- Ảnh sản phẩm: dùng ảnh gốc trên pccc24.com (đuôi `.png`, KHÔNG có hậu tố `-card`).

### 8. "Tin Tức PCCC"
Header: H2 "Tin Tức PCCC" + link "Tất cả bài viết ›". Lưới `repeat(auto-fit, minmax(min(100%,340px),1fr))`, gap 16px:
- Thẻ tin nổi bật: nền ink, chữ trắng, bo 22px, padding 24px, min-height 220px, nội dung dồn đáy. Nhãn "MỚI NHẤT · {ngày}" 12px/600 màu tag-yellow; tiêu đề 24px/700 lh 1.2. (Nếu bài có ảnh đại diện có thể đặt làm nền + lớp phủ tối.)
- Danh sách 3 bài tiếp theo: nền trắng bo 22px; dòng: thumbnail 76×56 bo 10px, tiêu đề 15px/500, ngày 13px ink-soft, mũi tên › bên phải. Phân cách giữa các dòng.

### 9. Footer
Container như main, `margin-top: 56px`, flex column gap 20px.
- H2 "Thông tin liên hệ".
- Hàng 1: lưới `repeat(auto-fit, minmax(min(100%,340px),1fr))`, gap 16px:
  - **Thông tin công ty**: nhãn trên "CÔNG TY TNHH MTV HOÀNG QUÂN PHÁT" (20px/600, uppercase, ink-soft, padding `0 16px`). Bên dưới danh sách nhóm nền trắng bo 18px:
    - Văn phòng đại diện (nhãn 12px ink-soft) / 262 Đỗ Mười, Phường Tam Bình, Thành phố Hồ Chí Minh (15px)
    - Trụ sở chính / Số 16, Đường N4, khu nhà ở TM DV Phú Mỹ, khu 5, phường Bình Dương, Thành phố Hồ Chí Minh
    - Điện thoại → **0982765114** (xanh exit, `tel:`)
    - Email → hoangquanphat@gmail.com (`mailto:`)
    - GPKD → 3701888205
  - **Bản đồ**: bo 18px, min-height 340px, iframe Google Maps phủ kín:
    `https://maps.google.com/maps?q=262%20%C4%90%E1%BB%97%20M%C6%B0%E1%BB%9Di%2C%20Tam%20B%C3%ACnh%2C%20Th%E1%BB%A7%20%C4%90%E1%BB%A9c%2C%20H%E1%BB%93%20Ch%C3%AD%20Minh&z=16&output=embed` (`loading="lazy"`, không viền).
    Nhãn kính mờ đè ở đáy (cách mép 12px, bo 14px): "Văn phòng đại diện" (14px bold) / "262 Đỗ Mười, P. Tam Bình, TP.HCM" (12px) + nút đỏ viên thuốc "Chỉ đường" → `https://www.google.com/maps/dir/?api=1&destination=262 Đỗ Mười, Tam Bình, Thủ Đức` (target _blank).
- Hàng 2: 3 nhóm link (lưới auto-fit min 240px): nhãn nhóm uppercase 13px + danh sách nhóm bo 14px, mỗi dòng 15px có ›.
  - Giới thiệu công ty: Giới thiệu, Liên hệ, Tin tức
  - Chính sách: Chính sách vận chuyển-giao nhận, Chính sách đổi trả hàng, Chính sách bảo mật
  - Quy định: Điều khoản hoạt động, Quy định bảo hành, Điều kiện sử dụng
- Dòng cuối: viền trên `1px solid rgba(29,35,41,.1)`, padding `16px 4px 24px`, 13px ink-soft. Trái: "© 2026 PCCC24 — Công ty TNHH MTV Hoàng Quân Phát." Phải: 3 nút viên thuốc nền trắng (padding `6px 12px 6px 8px`, gap 6px) có logo 18px:
  - Facebook – `https://cdn.simpleicons.org/facebook/1877F2` → facebook.com/pccc24
  - Youtube – `https://cdn.simpleicons.org/youtube/FF0000` → youtube.com/c/Pccc24HQP
  - Zalo – `https://cdn.simpleicons.org/zalo/0068FF` → zalo.me/0982765114
  (Nên tải SVG logo về theme thay vì gọi CDN.)

### 10. Thanh liên hệ nổi (mọi kích thước màn hình)
`position: fixed; bottom: 18px; left: 50%; transform: translateX(-50%); z-index: 30`. Viên thuốc kính mờ padding 6px gap 6px chứa:
- "Gọi báo giá" (icon điện thoại 16px) — nền exit, chữ trắng 15px/600, padding `11px 20px` → `tel:0982765114`
- "Zalo" — nền `#0068ff` → zalo.me/0982765114
Body cần `padding-bottom: 110px` để không che nội dung cuối.

### 11. Bottom sheet (Xem nhanh / Báo giá)
- Lớp phủ `rgba(29,35,41,.4)` full màn hình, căn sheet ở đáy (padding 12px). Bấm phủ → đóng. Esc → đóng.
- Sheet: max-width 560px, max-height 90vh, cuộn trong, nền concrete, bo 24px, padding `8px 18px 20px`, gap 14px. Animation vào: `translateY(40px)→0` + opacity, 320ms `cubic-bezier(.23,1,.32,1)`.
- Grabber 38×5 bo 999px `rgba(29,35,41,.2)`. Hàng tiêu đề 17px/600 + nút đóng tròn 30px nền `rgba(118,118,128,.16)`.
- Tiêu đề: "Xem nhanh" (từ thẻ sản phẩm) hoặc "Báo giá & Tư vấn PCCC" (từ nút header).
- Chế độ sản phẩm: ô ảnh trắng bo 18px (ảnh max 260px cao) + tên sản phẩm 20px/700.
- Danh sách: Bán hàng 0982765114 · Kỹ thuật 02866566422.
- Form gọi lại: nhãn "HOẶC ĐỂ LẠI SĐT — KỸ THUẬT VIÊN GỌI LẠI"; danh sách nhóm input (16px, padding `12px 16px`): Tên của bạn *, Số điện thoại *, select Nhu cầu (Bình chữa cháy / Thi công hệ thống / Bảo trì / Khác). Nút "Gửi yêu cầu gọi lại" full width nền đỏ bo 14px. Nối vào handler form hiện có của theme (giữ nguyên backend gửi mail/lead).
- Có thể dùng `<dialog>` sẵn có của theme, chỉ đổi style.

---

## Tương tác & hành vi
- Nút bấm: `:active { transform: scale(.97) }` 120ms ease-out (giữ quy tắc của theme).
- Slider: tự chạy 5s, bấm chấm để nhảy; tôn trọng `prefers-reduced-motion` (tắt tự chạy + transition).
- Segmented sản phẩm: đổi tab không reload trang.
- Ảnh lỗi: hiển thị ô placeholder kẻ sọc nhạt thay vì icon ảnh hỏng.
- Focus: giữ `:focus-visible { outline: 3px solid var(--tag) }` của theme.

## Responsive
Không dùng breakpoint cứng cho phần lớn layout — dùng `auto-fit/auto-fill + minmax(min(100%,X),1fr)` và `flex-wrap` như mô tả. Bổ sung:
- ≤ 900px: ẩn nav text trong header, thay bằng nút menu mở drawer (tái sử dụng `.drawer` của theme, style lại dạng danh sách nhóm iOS); ô tìm kiếm xuống hàng 2 full width.
- ≤ 640px: lưới sản phẩm 2 cột; thanh liên hệ nổi giữ nguyên dạng viên thuốc ở giữa.

## State cần có (JS thuần)
- `slideIndex` (slider), `activeTab` (sản phẩm), `sheet` (null | {mode:'quote'} | {mode:'product', name, img, url}).

## Tài sản (assets)
- Tất cả ảnh lấy từ `https://pccc24.com/uploads/` — dùng **file gốc** (đuôi `.png`/`.jpg`/`.webp` như ghi ở trên); bản mirror GitHub Pages đang trỏ tới các biến thể `-card.webp` / `-hero.webp` **không tồn tại** trên pccc24.com → cần sửa đường dẫn / tạo lại kích thước ảnh trong WP.
- Logo: `template-2-2026-03-24t1404074301774335857.png`.
- Logo MXH: Simple Icons (Facebook, YouTube, Zalo).

## Files
- `PCCC24 iOS.dc.html` — prototype trang chủ (mở trực tiếp trong trình duyệt).
- `support.js` — runtime cho prototype (chỉ để xem, không đưa vào theme).
