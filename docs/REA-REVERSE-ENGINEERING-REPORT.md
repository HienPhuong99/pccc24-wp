# 🔬 Hồ Sơ Dịch Ngược Toàn Diện `pccc24.com` (REA Reverse-Engineering Report & WordPress Theme Blueprint)

**Công cụ thực thi:** `rea-agents` (`rea@6.7.0` — [morluto/rea](https://github.com/morluto/rea)) + Phân tích tĩnh AST/Semantic Graph + Đối chiếu trực tiếp `D:\Code\PCCC24` & `https://pccc24.com` + Khảo sát MySQL `pccc24_db`.
**Thực hiện bởi:** Argon (Gemini) — Chặng 1 (Song song với Chặng 2 của Opus).

---

## 1. 🔐 Bằng Chứng Xác Thực từ REA (`rea analyze-javascript-application`)

Đã cài đặt global `rea-agents` (`rea@6.7.0`) và chạy phân tích toàn bộ mã nguồn ứng dụng gốc `D:\Code\PCCC24\src`:

```bash
rea analyze-javascript-application "D:/Code/PCCC24/src" --format json
```

### Kết quả Định danh & Hash Bằng chứng (Immutable Evidence Record)
- **`evidence_id`**: `ev_c82e9a8371ef0bf22abf4835b6810393840a7f5d3031ef5784efd2fecd6942b0`
- **`predicate_type`**: `rea.javascript-application-analysis`
- **`provider`**: `rea-javascript-application` v1 (`REA JavaScript application analyzer`)
- **`root_artifact_sha256`**: `39314fa1ef58ac1b862ffdc2e26011dcf3a6bd7e038d7b6aac8e61a03bea82a5`
- **`inventory_graph_sha256`**: `c836151d5ac7a01b89cff13264d01583c0c7f6472b696a8fd343489b0acd836b`
- **Thống kê AST & Đồ thị Ngữ nghĩa (Semantic Graph):**
  - Tổng số file JS/JSX phân tích: **`49/49 files`** (`parse_failures: 0`, `invalid_utf8_files: 0`)
  - Tổng dung lượng mã nguồn đọc: **`156,124 bytes`**
  - Số nút AST đã duyệt (`visited_ast_nodes`): **`22,471 nodes`**
  - Số phát hiện (`findings`): **`344 findings`**
  - Đồ thị ngữ nghĩa (`semantic_graph`): **`11,488 nodes`** và **`8,930 relations`**

---

## 2. 🕵️ Giải Đáp Các Câu Hỏi Kỹ Thuật Của Opus Ở Lượt `[#2]` (Từ Bằng Chứng REA & Code Gốc)

Trong lượt `[#2]`, Opus đã nêu 3 điểm cần Argon xác minh khi chạy REA:

### 2.1. Xác minh cơ chế lọc của `ProductFilter.jsx` & bảng `properties`
- **Trong `src/js/reactjs/components/products/ProductFilter.jsx` (dòng 1–89) & `src/js/function.js` (dòng 61–82):**
  - Component `ProductFilter` gắn vào `#filterProduct` (đọc `data-props` chứa `category_properties` có cấu trúc cha–con `property.childrens`).
  - Khi người dùng tích chọn các mục con (`PropertyItem.js`), mảng ID được lưu vào state `propertyActived` và khi bấm nút **"Áp dụng"**, nó gọi `window.applyFilter(propertyActived)` → đẩy query param `?propertyId=10,11` lên URL hiện tại.
- **Tuy nhiên, đối chiếu với `app/Views/categories/show.php` và `app/Controllers/CategoryController.php`:**
  - Trong `categories/show.php` hiện tại **không có thẻ `#filterProduct`** (chỉ có `.SortSearch` để sắp xếp theo `sorted=1..5`: Mặc định, A→Z, Z→A, Giá tăng dần, Giá giảm dần).
  - Trong `CategoryController::show($slug)`, câu truy vấn SQL chỉ lọc theo `category_id` (và các danh mục con `where('parent_id', $category->id)`) + `sorted`, **chưa hề nối bảng `properties` vào câu query lọc sản phẩm** (bảng `properties` chỉ có 15 dòng thử nghiệm cho `category_id = 56`: *"Khối lượng bột(kg): 1..9"* và *"Thời gian phun(s): ≥15..≥40"*, không có bảng trung gian nối `properties.id` với `products.id`).
  - Ngược lại, bảng **`product_properties` (379 dòng)** có cột `product_id`, `name`, `content` được `ProductController::show($slug, $id)` lấy ra để hiển thị **Bảng thông số kỹ thuật (`table.product-property-table`)** ngay trang chi tiết sản phẩm (`app/Views/products/show.php:78-87`)!
  - **Kết luận cho WordPress:** Cách Opus thiết kế lưu `product_properties` vào post meta `_pccc_specs` (mảng `[name, content]`) của `pccc_product` là **chính xác 100%**! Ngoài ra, trên trang danh mục của WordPress, chúng ta giữ nguyên bộ sắp xếp `.SortSearch` (`?sorted=1..5`) giống hệt trang thật `pccc24.com`, và có thể hỗ trợ thêm lọc theo `_pccc_specs` hoặc `?propertyId=` nếu cần.

### 2.2. Xác minh `CommentApp.jsx` (Vì sao không có bảng `comments` trong DB?)
- **Trong `src/js/reactjs/components/comments/CommentApp.jsx`, `CommentList.jsx`, `CommentForm.jsx`:**
  - `CommentApp` mount vào `#comment-box`, gọi `GET /v1/comments?object=...&object_id=...` và `POST /v1/comments`.
  - **Tuy nhiên:**
    1. Trong `src/js/app.js` (dòng 1–15), `CommentApp.jsx` **không được import** vào bundle.
    2. Trong `app/Views/products/show.php`, `articles/show.php`, `services/show.php`, **không có phần tử `#comment-box`** nào.
    3. Trong `app/Config/Routes.php`, **không có route `/v1/comments`** và cũng không có `CommentController` hay bảng `comments`.
  - **Kết luận:** `CommentApp.jsx`, `CartIndexApp.jsx`, `CheckoutIndexApp.jsx`, `Auth.jsx`, `AdApp.jsx`, `FooterApp.jsx` là các component mẫu từ bộ khung cũ được giữ lại trong `src/js/reactjs/components/` nhưng đã bị comment out trong `src/js/app.js` (dòng 7–13)!

### 2.3. Xác minh các Component React THỰC SỰ đang chạy trên `pccc24.com` (`src/js/app.js`)
Theo đồ thị import từ `src/js/app.js` (lines 1–14) và `src/js/function.js` (lines 1–2):
1. **`ProductPreview.js`** (gắn vào `#ProductPreviewApp` bên trong modal `#preview-box` ở cuối `layouts/app.php:267-271`):
   - Khi bấm nút mắt xem nhanh `onclick="previewProduct(id)"` trên thẻ sản phẩm (ở màn hình `> 768px`), hàm `window.previewProduct(id)` gọi `GET /v1/products/{id}` → mở modal `#preview-box` hiển thị ảnh, tên + link chi tiết, giá (`priceProduct`), và nội dung HTML (`product.content`).
   - Lưu ý nhỏ trong `ProductPreview.js:38`: bản gốc gọi `routePath('products.show', {slug, id})` trả về `/${slug}/${id}.html` (bị sai so với route thật `/san-pham/${slug}/${id}`). Khi chuyển sang WordPress, chúng ta sửa lại link trong popup Xem nhanh trỏ đúng về `/san-pham/${slug}/${id}`!
2. **`SortSearch.jsx`** (gắn vào `.SortSearch` trên trang `categories/show.php:27`):
   - Dropdown "Sắp xếp theo: Mặc định (5) | A -> Z (1) | Z -> A (2) | Giá tăng dần (3) | Giá giảm dần (4)".
   - Khi chọn thì đổi URL param `?sorted={id}` và bỏ `page`.
3. **`SupportApp.jsx`** (gắn vào `#SupportApp` bên trong widget góc phải dưới `layouts/support.php:15`):
   - Gọi `GET /v1/supports` → trả về `{ supports: [...] }` từ bảng `supports` (6 dòng: Kinh doanh, Bán hàng, Kỹ thuật, Email hỗ trợ, Kinh Doanh 1 Zalo, Kinh doanh 2 Zalo).
   - Render danh sách nút gọi điện (`tel:`), Zalo (`https://zalo.me/...`), Messenger (`https://m.me/...`), Email (`mailto:...`) trong popup "Liên hệ với chúng tôi" khi bấm nút tròn tím **Hỗ trợ** ở góc phải dưới.
4. **Giỏ hàng (`window.addCart(id, q=1)`):**
   - Trên mỗi thẻ sản phẩm có nút icon giỏ hàng `onclick="addCart(<?= $product->id ?>)"` gọi `POST /v1/carts` với `{ product_id, quantity }`.
   - Trong bản gốc CI4, route `POST /v1/carts` trỏ tới `V1\CartController::create` nhưng controller này bị thiếu nên bấm vào báo lỗi *"Hệ thống đang gặp sự cố"*.
   - **Trên bản WordPress `Pccc24-WP`:** Chúng ta nối `POST /v1/carts` (hoặc `/wp-json/pccc24/v1/carts`) và trang `/gio-hang` + `/thanh-toan` với CPT `pccc_order` để khách hàng bấm "Thêm vào giỏ hàng" hoạt động mượt mà 100%, hiển thị thông báo thành công (`CustomAlert`) và đặt hàng lưu thẳng vào `wp-admin`!

---

## 3. 🗺️ Bản Kiểm Kê Chi Tiết Toàn Bộ Views, Layouts, Routes & Quy Tắc Dữ Liệu (Blueprint cho Chặng 3 `pccc24-theme`)

### 3.1. Bảng Ánh Xạ URL & Controller Logic (Giữ nguyên 100% SEO & Link nội bộ)

| Đường dẫn URL (Routes.php) | View gốc (CI4) | Dữ liệu & Thứ tự sắp xếp (Query Logic) | Template / Xử lý trong `pccc24-theme` |
|---|---|---|---|
| `/` | `home/index.php` | - `sliders`: tất cả từ bảng `sliders` (`#sliders` OwlCarousel 1 item)<br>- `policies` (position=1, order by `sorted ASC`): 4 cam kết dưới slider (`#service-sliders` OwlCarousel 4 items)<br>- `sliders2` (`policies` có `position=2`, order by `sorted ASC`, limit 6): khu vực **"Dịch vụ nổi bật"** (5 vòng tròn tím `.home-support`)<br>- `categoriesHome`: các danh mục có `popular = 1` (`order by home_sorted ASC`), mỗi danh mục lấy tối đa **5 sản phẩm** (`WHERE category_id = ct.id ORDER BY sorted ASC, id DESC LIMIT 5`) chạy slider `.SLIDER-PRODUCT-CT` (5 items)<br>- `articles`: 4 bài viết mới nhất (`ORDER BY id DESC LIMIT 4`) | `front-page.php` |
| `/san-pham` | `products/index.php` *(Ghi chú: trong CI4 gốc file view `products/index.php` bị thiếu, nên dùng chung bố cục lưới sản phẩm của `categories/show.php`)* | Phân trang 20 sản phẩm/trang (`ORDER BY sorted ASC, id DESC`) + sidebar danh mục & 5 SP nổi bật (`popular = 1`) | `archive-pccc_product.php` |
| `/san-pham/tim-kiem?q=...` | `products/search.php` | Tìm `LIKE %q%` theo tên sản phẩm, phân trang 20 SP/trang + sidebar `categories/left` (kèm 5 SP `popular = 1`) | `search-product.php` (hoặc xử lý trong router/template) |
| `/san-pham/{cat-slug}` | `categories/show.php` | Tìm danh mục theo `slug`. Lấy SP thuộc `category_id` **HOẶC thuộc các danh mục con** (`parent_id = category.id`).<br>Hỗ trợ `?sorted=`: `1` (`name ASC`), `2` (`name DESC`), `3` (`price ASC`), `4` (`price DESC`), mặc định (`sorted ASC, created_at DESC`). Phân trang 20 SP/trang.<br>Cuối trang hiển thị bài mô tả dài `$category->content` (nếu có). | `taxonomy-pccc_product_cat.php` |
| `/san-pham/{slug}/{id}` | `products/show.php` | Tìm SP theo `_pccc_old_id = {id}` (hoặc `slug`).<br>- Breadcrumb: `Trang chủ > [Danh mục cha] > Danh mục con`<br>- Ảnh chính có class `lightbox` (`data-href`, `data-id="lightbox-{id}"`)<br>- Giá: `> 0` hiện `priceFormat($price)đ`, `= 0` hiện `Liên hệ`<br>- Bảng thông số kỹ thuật `.product-property-table` từ `_pccc_specs` (`379 dòng`)<br>- Hotline bán hàng (`phone_sale`) & kỹ thuật (`phone_technique`)<br>- Khối `.product-policy` (nếu SP có liên kết trong `product_policy_products`)<br>- Nút chia sẻ Facebook, Twitter, LinkedIn<br>- Phần **Thông tin sản phẩm** (`.content-body` chứa `$product->content`) | `single-pccc_product.php` |
| `/dich-vu` | `services/index.php` | Tất cả dịch vụ, sắp xếp đặc biệt: `ORDER BY (sorted = 0) ASC, sorted ASC, id DESC` (ép `sorted = 0` xuống cuối, ưu tiên 1, 2, 3... rồi tới bài mới nhất). Phân trang 20 bài/trang + sidebar `services/left` (3 loại dịch vụ). | `archive-pccc_service.php` |
| `/dich-vu/{type-slug}` | `services/index.php` | Lọc dịch vụ theo `service_type` (`slug`), cùng quy tắc sắp xếp `(sorted = 0) ASC, sorted ASC, id DESC`. | `taxonomy-pccc_service_type.php` |
| `/dich-vu/{slug}/{id}` | `services/show.php` | Tìm dịch vụ theo `_pccc_old_id = {id}`. Nếu `slug` trên URL lệch với `service->slug` thì redirect 301 về URL chuẩn. Hiển thị breadcrumb, ngày đăng (`d/m/Y`), tiêu đề `<h1>`, nội dung HTML và thẻ `canonical` / `meta robots`. | `single-pccc_service.php` |
| `/tin-tuc` | `articles/index.php` | Danh sách bài viết (`post`), `ORDER BY id DESC`, phân trang 20 bài/trang + sidebar `articles/left` (5 bài viết có `popular = 1`). | `home.php` / `archive-article.php` |
| `/tin-tuc/{slug}/{id}` | `articles/show.php` | Chi tiết bài viết theo `_pccc_old_id = {id}` và `slug`. Có JSON-LD `@type: Article` (tác giả `Nguyễn Phú Toàn`, url `/pages/tac-gia-nguyen-phu-toan`, publisher `PCCC24`), sidebar 5 tin nổi bật (`popular = 1`). | `single.php` |
| `/pages/type/{slug}` | `pages/category.php` | Danh sách các trang thuộc nhóm `pccc_page_type` (`gioi-thieu-cong-ty`, `chinh-sach`, `quy-dinh`), **loại trừ** các trang có `slug LIKE 'tac-gia-%'`. | `taxonomy-pccc_page_type.php` |
| `/pages/{slug}` | `pages/show.php` | Chi tiết trang tĩnh (`page`), sidebar trái liệt kê các trang cùng `page_type` (trừ `tac-gia-%`). | `page.php` |
| `/contacts` | `contacts/index.php` | Trang Liên hệ hiển thị thông tin công ty, địa chỉ, điện thoại, email, website, GPKD từ `settings` (và có thể bổ sung form gửi liên hệ lưu vào `pccc_order` / liên hệ). | `page-contacts.php` |

---

### 3.2. Cấu Trúc Layout Chung (`app/Views/layouts/app.php` & `support.php`)

1. **Header (`<header class="header pt-2">`):**
   - **Top Header:**
     - Trái (`lg-g:w-2/5`): Nút hamburger `.show-menu` (chỉ hiện khi `< 890px`) + Logo (`getSettingKey('logo')`).
     - Phải (`lg-g:w-3/5`, ẩn khi `< 890px`): 3 cột Hotline (**Hỗ trợ bán hàng** `phone_sale`, **Hỗ trợ kỹ thuật** `phone_technique`, **Email báo giá** `email`) + Tên công ty (`company`) + Địa chỉ văn phòng & trụ sở (`address` — cho phép HTML).
   - **Navigation Bar đỏ (`bg-red-pccc` = `#f71616`):**
     - Nút **DANH MỤC SẢN PHẨM** (`bg-rose-600 p-3 has-dropdown`): Hover xổ menu cấp 1 (`getInitCategory()` — 11 danh mục gốc), hover mục cấp 1 xổ menu cấp 2 bên phải (`border-2 border-orange-400`).
     - Menu ngang (`.main-menu`):
       - 3 mục từ `serviceType()` (`enabled = 1`, `ORDER BY sorted ASC`):
         1. `Thi Công Lắp Đặt Hệ Thống PCCC` (`/dich-vu/thi-cong-lap-dat-he-thong-pccc`)
         2. `Bảo Trì Sửa Chữa Hệ Thống PCCC` (`/dich-vu/bao-tri-sua-chua-he-thong-pccc`)
         3. `Dự Án` (`/dich-vu/du-an-`)
       - `Tin tức` (`/tin-tuc`)
       - `Về chúng tôi` (`/pages/gioi-thieu`)
     - Ô tìm kiếm bên phải (`form action="/san-pham/tim-kiem"` với `input name="q"`).
2. **Menu Mobile Drawer (`.menu-mobile`):**
   - *Lưu ý cải tiến quan trọng:* Trong code CI4 gốc (`layouts/app.php:272`), khối `.menu-mobile` bị bọc trong `<?php if (checkMobileAgent()) : ?>` dựa vào HTTP User-Agent, khiến trình duyệt máy tính thu nhỏ màn hình hoặc trình kiểm thử responsive không mở được menu mobile nếu không đổi User-Agent!
   - **Trong WordPress Theme (`pccc24-theme`):** Luôn render sẵn khối `.menu-mobile.hidden` trong DOM (vì nó đã có class `hidden` mặc định, chỉ mở khi bấm `.show-menu` ở màn hình `< 890px`), giúp hoạt động hoàn hảo 100% trên cả thiết bị di động thật lẫn khi resize trình duyệt / test Playwright!
3. **Footer (`<footer class="bg-slate-100 pt-4 pb-4">`):**
   - Lưới 4 cột (`grid md:grid-cols-4 sm-m:grid-cols-2 gap-5`):
     - 3 cột đầu lấy từ `getInitFooter()` (`page_types` có `position = 2`: **Giới thiệu công ty**, **Chính sách**, **Quy định**) và các trang con thuộc từng nhóm (loại trừ `tac-gia-%`, nếu trang có trường `url` riêng như `Liên hệ -> /contacts`, `Tin tức -> /tin-tuc` thì dùng trường `url`).
     - Cột thứ 4: **Follow us** (icon SVG Facebook, TikTok, YouTube từ `settings`) + **Bản đồ** (Google Maps iframe Công ty PCCC Hoàng Quân Phát).
   - Khối `.info-footer` căn giữa dưới cùng: Tên công ty, Văn phòng đại diện, Điện thoại, Email, Website, GPKD (`3701888205`).
4. **Floating Widgets (`layouts/support.php`):**
   - Modal xem nhanh `#preview-box` (`#ProductPreviewApp`).
   - Nút tròn tím góc phải dưới `.main-widget` (**Hỗ trợ** + `#SupportApp`).
   - 2 nút Zalo nổi `.zalo-live-fixed` lấy 2 dòng đầu của `getZaloSupports()` (`support_type = 'zalo'`) với ảnh `/images/zalonew_1.png` và `/images/zalonew_2.png`.
   - Nút cuộn lên đầu trang `.scroll-top.scroll-to-target`.

---

### 3.3. Hệ Thống CSS, Breakpoints & Assets Cần Copy Nguyên Bản Sang Theme

- **Custom Breakpoints của Tailwind trong `pccc24.com` (`tailwind.config.js`):**
  - `xs-s`: `100px`
  - `xs`: `380px`
  - `sm-m`: `560px`
  - `sm`: `640px`
  - `md`: `768px`
  - `lg-g`: `890px` (Breakpoint chính chuyển đổi Header/Menu giữa Desktop và Mobile!)
  - `lg`: `1024px`
- **Assets có sẵn trong `D:\Code\PCCC24\public\`:**
  - `public/css/app.css` (đã biên dịch đầy đủ Tailwind + `style.css` + `styles.css` + `response.css` + `owl.carousel.css` + `lightbox.css` + `paginator.css` + `custom.css`)
  - `public/js/app.js` (bundle Webpack Mix gồm Axios, OwlCarousel, React widgets, Lightbox, CustomAlert)
  - `public/static/jquery-3.5.1.min.js` & `public/static/boxicons/css/boxicons.min.css` (+ fonts boxicons)
  - `public/images/` (chứa `no-image.png`, `zalonew_1.png`, `zalonew_2.png`, icons...)
  - `public/uploads/` (chứa toàn bộ ảnh sản phẩm, bài viết, dịch vụ, slider, chính sách, logo!)
  - `public/favicon.ico`

> **Chiến lược Assets siêu sạch cho WordPress (`D:\Code\Pccc24-WP`):**
> - Copy nguyên vẹn `public/css`, `public/js`, `public/static`, `public/images`, `public/favicon.ico` vào gốc WordPress (hoặc thư mục theme + giữ symlink/copy ở `/static`, `/images`, `/uploads`, `/css`, `/js`) để **mọi đường dẫn tương đối trong CSS/JS/HTML bài viết cũ (`/uploads/...`, `/images/...`, `/static/...`) đều tải lên tức thì 100% không một lỗi 404 nào!**
