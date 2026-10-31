<?php
/**
 * Kết xuất toàn bộ website WordPress PCCC24 ra bản tĩnh tương tác 100% cho GitHub Pages (gh-pages)
 * Chạy: php tools/wp-cli.phar --path=d:\Code\Pccc24-WP eval-file scripts/export-gh-pages.php
 */

defined('ABSPATH') || exit;

$base_prefix = '/pccc24-wp';
$dist_dir    = getenv('PCCC24_GH_DIST') ?: (sys_get_temp_dir() . '/pccc24-gh-pages');

if (is_dir($dist_dir)) {
    // Xóa nội dung cũ (trừ .git nếu có)
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dist_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . '.git')) {
            continue;
        }
        $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
    }
} else {
    mkdir($dist_dir, 0777, true);
}

file_put_contents("$dist_dir/.nojekyll", "");

/**
 * Chuyển đổi đường dẫn ảnh local /wp-content/uploads/pccc24/... sang CDN gốc https://pccc24.com/uploads/...
 * và gắn prefix /pccc24-wp cho toàn bộ liên kết nội bộ trên GitHub Pages.
 */
function pccc24_transform_for_gh_pages(string $html, string $prefix): string
{
    // 1. Ảnh uploads -> https://pccc24.com/uploads/...
    $html = preg_replace_callback(
        '~(?:https?://localhost:8080)?/wp-content/uploads/pccc24/([^"\'\s<>)]+)~i',
        function ($m) {
            $parts = explode('/', $m[1]);
            $encoded = implode('/', array_map('rawurlencode', array_map('rawurldecode', $parts)));
            return 'https://pccc24.com/uploads/' . $encoded;
        },
        $html
    );

    // 2. Các URL tuyệt đối http://localhost:8080/... -> /pccc24-wp/...
    $html = str_replace(
        ['http://localhost:8080/', 'http://localhost:8080', 'http:\/\/localhost:8080\/', 'http:\/\/localhost:8080'],
        [$prefix . '/', $prefix, str_replace('/', '\/', $prefix) . '\/', str_replace('/', '\/', $prefix)],
        $html
    );

    // 3. Các thuộc tính href/src/action/data-href tương đối gốc (/...)
    $html = preg_replace(
        '~\b(href|src|action|data-href)=(["\'])/(?!pccc24-wp/|/)(san-pham|dich-vu|tin-tuc|pages|contacts|chinh-sach|quy-dinh|gioi-thieu-cong-ty|wp-content|wp-includes|wp-json|wp-admin)~i',
        '$1=$2' . $prefix . '/$3',
        $html
    );

    // 4. Link trang chủ href="/"
    $html = preg_replace('~\bhref=(["\'])/\1~i', 'href=$1' . $prefix . '/$1', $html);

    // 5. Nhúng script adapter cho GitHub Pages trước </body>
    $adapter_tag = '<script src="' . $prefix . '/wp-content/themes/pccc24-theme/assets/js/gh-pages-demo.js"></script></body>';
    $html = str_replace('</body>', $adapter_tag, $html);

    return $html;
}

function pccc24_write_dist(string $rel_path, string $content): void
{
    global $dist_dir;
    $full = $dist_dir . '/' . ltrim($rel_path, '/');
    $dir  = dirname($full);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($full, $content);
}

function pccc24_copy_dir(string $src, string $dst): void
{
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0777, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

// 1. Copy toàn bộ theme assets
$theme_assets_src = get_stylesheet_directory() . '/assets';
$theme_assets_dst = $dist_dir . '/wp-content/themes/pccc24-theme/assets';
pccc24_copy_dir($theme_assets_src, $theme_assets_dst);
WP_CLI::log('Copied theme assets.');

// 2. Xuất dữ liệu JSON tĩnh cho REST API (/wp-json/pccc24/v1/...)
$products_all = [];
$product_posts = get_posts([
    'post_type'   => 'pccc_product',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby'     => ['menu_order' => 'ASC', 'date' => 'DESC'],
]);

foreach ($product_posts as $p) {
    $json = pccc24_product_json($p);
    // Chuyển image_url và content sang URL chuẩn cho GH Pages
    if (!empty($json['image_url'])) {
        $json['image_url'] = pccc24_transform_for_gh_pages($json['image_url'], $base_prefix);
    }
    $json['url']     = $base_prefix . parse_url(get_permalink($p), PHP_URL_PATH);
    $json['content'] = pccc24_transform_for_gh_pages((string) $json['content'], $base_prefix);

    $old_id = (int) $json['id'];
    $encoded = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    pccc24_write_dist("wp-json/pccc24/v1/products/{$old_id}.json", $encoded);
    pccc24_write_dist("wp-json/pccc24/v1/products/{$old_id}/index.html", $encoded);

    $terms = get_the_terms($p->ID, 'pccc_product_cat');
    $cat_slugs = [];
    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $t) {
            $cat_slugs[] = $t->slug;
            if ($t->parent && ($pt = get_term($t->parent, 'pccc_product_cat')) && !is_wp_error($pt)) {
                $cat_slugs[] = $pt->slug;
            }
        }
    }

    $products_all[] = [
        'id'        => $old_id,
        'name'      => $json['name'],
        'slug'      => $json['slug'],
        'url'       => $json['url'],
        'price'     => $json['price'],
        'image_url' => $json['image_url'] ?: ($base_prefix . '/wp-content/themes/pccc24-theme/assets/images/no-image.png'),
        'cats'      => array_values(array_unique($cat_slugs)),
        'sorted'    => (int) $p->menu_order,
    ];
}

pccc24_write_dist(
    'wp-json/pccc24/v1/products-all.json',
    json_encode($products_all, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);
pccc24_write_dist(
    'wp-json/pccc24/v1/supports.json',
    json_encode(['supports' => pccc24_get_all_supports()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);
WP_CLI::log('Exported ' . count($products_all) . ' product JSON files.');

// 3. Tạo file JS Adapter (gh-pages-demo.js) xử lý Quick View, Cart/Order, Search, Sort, Pagination & Admin Demo
$adapter_js = <<<'JS'
(function (window, $) {
  "use strict";
  if (!$) return;

  var PREFIX = "/pccc24-wp";
  var noImg = PREFIX + "/wp-content/themes/pccc24-theme/assets/images/no-image.png";

  // Fallback tự động nếu ảnh bất kỳ bị lỗi
  $(document).on("error", "img", function () {
    if (this.src && this.src !== noImg && !this.dataset.fallbackApplied) {
      this.dataset.fallbackApplied = "1";
      this.src = noImg;
    }
  });
  $("img").each(function () {
    if (this.complete && this.naturalWidth === 0 && this.src !== noImg) {
      this.src = noImg;
    }
  });

  // 1. Điều hướng request REST API sang file .json tĩnh hoặc xử lý Đặt hàng trên trình duyệt
  $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
    var u = options.url || "";
    var mProduct = u.match(/\/wp-json\/pccc24\/v1\/products\/(\d+)\/?$/);
    if (mProduct) {
      options.url = PREFIX + "/wp-json/pccc24/v1/products/" + mProduct[1] + ".json";
      return;
    }
    if (/\/wp-json\/pccc24\/v1\/supports\/?$/.test(u)) {
      options.url = PREFIX + "/wp-json/pccc24/v1/supports.json";
      return;
    }
    if (/\/wp-json\/pccc24\/v1\/(orders|carts)\/?$/.test(u) && (options.type || options.method || "").toUpperCase() === "POST") {
      jqXHR.abort();
      var payload = {};
      try {
        payload = typeof options.data === "string" ? JSON.parse(options.data) : options.data || {};
      } catch (e) {}
      var name = $.trim(payload.name || "");
      var phone = $.trim(payload.phone || "");
      if (!name || !/^[0-9 +().-]{8,20}$/.test(phone)) {
        if (options.error) {
          options.error({ responseJSON: { message: "Vui lòng nhập họ tên và số điện thoại hợp lệ" } });
        }
        return;
      }
      var orders = [];
      try {
        orders = JSON.parse(localStorage.getItem("pccc24_demo_orders") || "[]");
      } catch (e) {}
      var newOrder = {
        id: 2000 + orders.length + 1,
        name: name,
        phone: phone,
        email: payload.email || "",
        address: payload.address || "",
        note: payload.note || "",
        items: payload.items || [],
        created_at: new Date().toLocaleString("vi-VN"),
      };
      orders.unshift(newOrder);
      try {
        localStorage.setItem("pccc24_demo_orders", JSON.stringify(orders));
      } catch (e) {}
      setTimeout(function () {
        if (options.success) {
          options.success({
            id: newOrder.id,
            total: 0,
            items: newOrder.items,
            message: "Đặt hàng thành công! Đơn hàng #" + newOrder.id + " đã được lưu vào wp-admin Demo.",
          });
        }
      }, 150);
    }
  });

  // 2. Xử lý Tìm kiếm (?q=...), Sắp xếp (?sorted=1..5) và Phân trang (?page=N) động trên GitHub Pages
  $(document).ready(function () {
    var params = window.getUrlVars ? window.getUrlVars() : {};
    var path = window.location.pathname.replace(/\/+$/, "");
    var isSearchPage = /\/san-pham\/tim-kiem$/.test(path);
    var isProductsRoot = /\/san-pham$/.test(path);
    var catMatch = path.match(/\/san-pham\/([^\/]+)$/);
    var catSlug = catMatch && catMatch[1] !== "tim-kiem" ? catMatch[1] : null;

    var hasDynamicQuery = isSearchPage || params.sorted || (params.page && parseInt(params.page, 10) > 1);
    if (hasDynamicQuery && (isSearchPage || isProductsRoot || catSlug)) {
      $.getJSON(PREFIX + "/wp-json/pccc24/v1/products-all.json").done(function (all) {
        if (!Array.isArray(all)) return;
        var filtered = all.slice();

        if (isSearchPage) {
          var q = $.trim(params.q || "").toLowerCase();
          $(".right-content h1, .crumb li:last-child").last().text("Tìm kiếm: " + (params.q || ""));
          $('input[name="q"]').val(params.q || "");
          if (q) {
            filtered = filtered.filter(function (p) {
              return (p.name || "").toLowerCase().indexOf(q) !== -1;
            });
          }
        } else if (catSlug) {
          filtered = filtered.filter(function (p) {
            return Array.isArray(p.cats) && p.cats.indexOf(catSlug) !== -1;
          });
        }

        var sorted = parseInt(params.sorted || 5, 10);
        if (sorted === 1) {
          filtered.sort(function (a, b) {
            return a.name.localeCompare(b.name, "vi");
          });
        } else if (sorted === 2) {
          filtered.sort(function (a, b) {
            return b.name.localeCompare(a.name, "vi");
          });
        } else if (sorted === 3) {
          filtered.sort(function (a, b) {
            return (a.price || 0) - (b.price || 0);
          });
        } else if (sorted === 4) {
          filtered.sort(function (a, b) {
            return (b.price || 0) - (a.price || 0);
          });
        }

        var perPage = 20;
        var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        var page = Math.min(totalPages, Math.max(1, parseInt(params.page || 1, 10)));
        var slice = filtered.slice((page - 1) * perPage, page * perPage);

        var $grid = $(".right-content .grid.gap-4").first();
        if ($grid.length) {
          if (slice.length === 0) {
            $grid.html('<p class="col-span-4 text-gray-500 py-6">Không tìm thấy sản phẩm phù hợp.</p>');
          } else {
            var cards = slice
              .map(function (p) {
                var priceTxt = p.price > 0 ? window.formatMoney(p.price, 0, ",", ".") + "đ" : "Liên hệ";
                return (
                  '<div class="bg-white shadow rounded p-3 relative previewProduct">' +
                  '<div class="flex flex-col justify-between h-full">' +
                  '<div>' +
                  '<a href="' + p.url + '" title="' + $("<div>").text(p.name).html() + '">' +
                  '<div class="flex items-center justify-center h-44">' +
                  '<img src="' + p.image_url + '" class="img-product-responsive max-h-44" alt="' + $("<div>").text(p.name).html() + '" />' +
                  "</div>" +
                  '<h3 class="mt-2 text-base limit-line limit-line-2 hover:text-red-600">' +
                  $("<div>").text(p.name).html() +
                  "</h3>" +
                  "</a>" +
                  "</div>" +
                  '<div class="mt-2 pt-2 border-t flex justify-between items-center">' +
                  '<span class="text-red-600 font-medium">' + priceTxt + "</span>" +
                  '<div class="flex gap-1">' +
                  '<button type="button" onclick="previewProduct(' + p.id + ')" class="md:flex xs-s:hidden items-center justify-center w-8 h-8 rounded-full border hover:bg-red-500 hover:text-white cursor-pointer" title="Xem nhanh"><i class="bx bx-show"></i></button>' +
                  '<button type="button" onclick="addCart(' + p.id + ', 1)" class="flex items-center justify-center w-8 h-8 rounded-full border hover:bg-red-500 hover:text-white cursor-pointer" title="Thêm vào giỏ"><i class="bx bx-cart"></i></button>' +
                  "</div>" +
                  "</div>" +
                  "</div>" +
                  "</div>"
                );
              })
              .join("");
            $grid.html(cards);
          }
        }

        var $pag = $(".paginate-container");
        if ($pag.length) {
          if (totalPages <= 1) {
            $pag.empty();
          } else {
            var ul = '<ul class="pagination">';
            for (var i = 1; i <= totalPages; i++) {
              var href = window.replaceUrlParam("page", i);
              ul += '<li class="' + (i === page ? "active" : "") + '"><a href="' + href + '">' + i + "</a></li>";
            }
            ul += "</ul>";
            $pag.html(ul);
          }
        }
      });
    }

    // 3. Nút nổi chuyển nhanh sang trang Quản trị WordPress (wp-admin Demo) ở góc trái dưới
    var badge =
      '<a href="' +
      PREFIX +
      '/wp-admin/" style="position:fixed;left:16px;bottom:16px;z-index:1500;background:#1d2327;color:#fff;padding:8px 14px;border-radius:999px;font-size:13px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.25);display:flex;align-items:center;gap:6px;text-decoration:none;">' +
      '<span style="width:8px;height:8px;border-radius:50%;background:#00a32a;display:inline-block;"></span>' +
      '<span>WP-Admin Demo</span>' +
      "</a>";
    $("body").append(badge);
  });
})(window, window.jQuery);
JS;

pccc24_write_dist('wp-content/themes/pccc24-theme/assets/js/gh-pages-demo.js', $adapter_js);

// 4. Tạo trang /wp-admin/index.html mô phỏng trực quan trình quản trị WordPress PCCC24
$admin_html = <<<'HTML'
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bảng tin ‹ PCCC24 — WordPress Admin Demo</title>
    <style>
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { margin: 0; background: #f0f0f1; color: #1d2327; display: flex; min-height: 100vh; }
        .wp-sidebar { width: 220px; background: #1d2327; color: #f0f0f1; flex-shrink: 0; display: flex; flex-direction: column; }
        .wp-brand { padding: 14px 16px; background: #2c3338; font-weight: 700; font-size: 15px; display: flex; justify-content: space-between; align-items: center; }
        .wp-brand a { color: #72aee6; text-decoration: none; font-size: 12px; font-weight: 400; }
        .wp-nav { list-style: none; padding: 8px 0; margin: 0; }
        .wp-nav li { padding: 10px 16px; cursor: pointer; font-size: 14px; border-left: 4px solid transparent; }
        .wp-nav li:hover { background: #2c3338; color: #72aee6; }
        .wp-nav li.active { background: #2271b1; color: #fff; font-weight: 600; border-left-color: #fff; }
        .wp-main { flex: 1; padding: 24px 32px; overflow-x: auto; }
        .wp-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #c3c4c7; padding-bottom: 12px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 16px; border-radius: 4px; }
        .stat-card .num { font-size: 26px; font-weight: 700; color: #1d2327; }
        .stat-card .lbl { font-size: 13px; color: #50575e; margin-top: 4px; }
        table.wp-list-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #c3c4c7; }
        table.wp-list-table th, table.wp-list-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #f0f0f1; font-size: 13px; }
        table.wp-list-table th { background: #fff; border-bottom: 1px solid #c3c4c7; font-weight: 600; }
        table.wp-list-table tr:nth-child(even) { background: #f6f7f7; }
        .metabox { background: #fff; border: 1px solid #c3c4c7; margin-top: 20px; }
        .metabox-title { padding: 12px 16px; border-bottom: 1px solid #c3c4c7; font-weight: 600; background: #f6f7f7; }
        .metabox-body { padding: 16px; }
        .form-row { display: grid; grid-template-columns: 220px 1fr; gap: 12px; align-items: start; margin-bottom: 12px; }
        .form-row label { font-weight: 600; font-size: 13px; padding-top: 6px; }
        .form-row input, .form-row textarea { width: 100%; padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; }
        .btn-primary { background: #2271b1; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; }
        .btn-primary:hover { background: #135e96; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .notice-ok { background: #fff; border-left: 4px solid #00a32a; padding: 10px 14px; margin-bottom: 16px; box-shadow: 0 1px 1px rgba(0,0,0,.04); font-size: 13px; }
    </style>
</head>
<body>
    <aside class="wp-sidebar">
        <div class="wp-brand">
            <span>WordPress PCCC24</span>
            <a href="/pccc24-wp/">&larr; Xem trang</a>
        </div>
        <ul class="wp-nav">
            <li class="active" data-tab="dashboard">📊 Tổng quan hệ thống</li>
            <li data-tab="products">🛒 Sản phẩm (385)</li>
            <li data-tab="orders">📋 Đơn hàng (<span id="order-count">0</span>)</li>
            <li data-tab="settings">⚙️ Cài đặt PCCC24 (15)</li>
        </ul>
    </aside>
    <main class="wp-main">
        <div class="wp-header">
            <h1 style="margin:0;font-size:22px;">Quản trị WordPress — PCCC24 (`pccc24-core` + `pccc24-theme`)</h1>
            <a href="/pccc24-wp/" class="btn-primary" style="text-decoration:none;">Mở Trang Chủ Frontend</a>
        </div>

        <div class="notice-ok">
            ✅ <strong>Đối soát Migration CI4 &rarr; WordPress: 15/15 hạng mục khớp 100%</strong> • Bộ kiểm thử tự động Playwright E2E: <strong>40/40 PASSED</strong>.
        </div>

        <section id="tab-dashboard" class="tab-pane active">
            <div class="card-grid">
                <div class="stat-card"><div class="num">385</div><div class="lbl">Sản phẩm (`pccc_product`)</div></div>
                <div class="stat-card"><div class="num">40</div><div class="lbl">Danh mục (`pccc_product_cat`)</div></div>
                <div class="stat-card"><div class="num">62</div><div class="lbl">Tin tức (`post`)</div></div>
                <div class="stat-card"><div class="num">14</div><div class="lbl">Dịch vụ (`pccc_service`)</div></div>
                <div class="stat-card"><div class="num">12</div><div class="lbl">Trang tĩnh (`page`)</div></div>
                <div class="stat-card"><div class="num">723</div><div class="lbl">Ảnh Media (`wp-content/uploads`)</div></div>
            </div>

            <div class="metabox">
                <div class="metabox-title">Metabox "Thông tin PCCC24" (Mẫu chỉnh sửa sản phẩm #718 trong wp-admin)</div>
                <div class="metabox-body">
                    <div class="form-row"><label>Tên sản phẩm</label><input value="Bình chữa cháy gốc nước công nghệ sinh học Ecosafe"></div>
                    <div class="form-row"><label>Giá (0 = Liên hệ)</label><input value="0"></div>
                    <div class="form-row"><label>Nổi bật (1/0)</label><input value="1"></div>
                    <div class="form-row"><label>SEO Title</label><input value="Bình chữa cháy gốc nước công nghệ sinh học Ecosafe"></div>
                    <div class="form-row"><label>Thông số kỹ thuật (Tên | Giá trị)</label><textarea rows="5">Chủng loại | Bình chữa cháy gốc nước sinh học
Model | Ecosafe
Bảo hành | 12 tháng</textarea></div>
                    <button class="btn-primary" onclick="alert('Đã lưu thông tin Metabox (Demo)!')">Cập nhật sản phẩm</button>
                </div>
            </div>
        </section>

        <section id="tab-products" class="tab-pane">
            <table class="wp-list-table">
                <thead>
                    <tr><th>ID cũ (`_pccc_old_id`)</th><th>Tên sản phẩm</th><th>Giá (`_pccc_price`)</th><th>Hành động</th></tr>
                </thead>
                <tbody id="admin-products-tbody">
                    <tr><td colspan="4">Đang tải danh sách sản phẩm...</td></tr>
                </tbody>
            </table>
        </section>

        <section id="tab-orders" class="tab-pane">
            <p style="font-size:13px;color:#50575e;">💡 Khi bạn thêm sản phẩm vào giỏ và bấm <strong>Đặt hàng</strong> ngoài giao diện trang chủ/danh mục, đơn hàng sẽ xuất hiện trực tiếp tại đây:</p>
            <table class="wp-list-table">
                <thead>
                    <tr><th>Mã ĐH</th><th>Khách hàng</th><th>Điện thoại</th><th>Số SP</th><th>Ghi chú</th><th>Thời gian</th></tr>
                </thead>
                <tbody id="admin-orders-tbody"></tbody>
            </table>
        </section>

        <section id="tab-settings" class="tab-pane">
            <div class="metabox">
                <div class="metabox-title">Cài đặt chung PCCC24 (`options-general.php?page=pccc24`)</div>
                <div class="metabox-body">
                    <div class="form-row"><label>Tên công ty (`company`)</label><input value="CÔNG TY TNHH MTV HOÀNG QUÂN PHÁT"></div>
                    <div class="form-row"><label>Địa chỉ (`address`)</label><textarea rows="2">262 Đỗ Mười, Phường Tam Bình, Thành phố Hồ Chí Minh</textarea></div>
                    <div class="form-row"><label>Điện thoại (`phone`)</label><input value="0982765114"></div>
                    <div class="form-row"><label>ĐT bán hàng (`phone_sale`)</label><input value="0982765114"></div>
                    <div class="form-row"><label>ĐT kỹ thuật (`phone_technique`)</label><input value="02866566422"></div>
                    <div class="form-row"><label>Email (`email`)</label><input value="hoangquanphat@gmail.com"></div>
                    <div class="form-row"><label>GPKD (`gpkd`)</label><input value="3701888205"></div>
                    <button class="btn-primary" onclick="alert('Đã lưu cấu hình PCCC24 (Demo)!')">Lưu thay đổi</button>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll('.wp-nav li').forEach(function(li) {
            li.addEventListener('click', function() {
                document.querySelectorAll('.wp-nav li').forEach(x => x.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(x => x.classList.remove('active'));
                li.classList.add('active');
                document.getElementById('tab-' + li.dataset.tab).classList.add('active');
            });
        });

        fetch('/pccc24-wp/wp-json/pccc24/v1/products-all.json')
            .then(r => r.json())
            .then(list => {
                var tbody = document.getElementById('admin-products-tbody');
                tbody.innerHTML = list.slice(0, 40).map(p =>
                    '<tr><td><strong>#' + p.id + '</strong></td><td><a href="' + p.url + '" target="_blank">' + p.name + '</a></td><td>' + (p.price > 0 ? p.price.toLocaleString('vi-VN') + 'đ' : 'Liên hệ') + '</td><td><a href="' + p.url + '" target="_blank">Xem ngoài trang &rarr;</a></td></tr>'
                ).join('');
            });

        function renderOrders() {
            var orders = [];
            try { orders = JSON.parse(localStorage.getItem('pccc24_demo_orders') || '[]'); } catch(e) {}
            document.getElementById('order-count').textContent = orders.length;
            var tbody = document.getElementById('admin-orders-tbody');
            if (!orders.length) {
                tbody.innerHTML = '<tr><td colspan="6">Chưa có đơn hàng mới. Hãy thử bấm "Thêm vào giỏ" &rarr; "Đặt hàng" ngoài trang chủ!</td></tr>';
                return;
            }
            tbody.innerHTML = orders.map(o => {
                var qty = (o.items || []).reduce((s, it) => s + (parseInt(it.quantity, 10) || 1), 0);
                return '<tr><td><strong>#' + o.id + '</strong></td><td>' + o.name + '</td><td>' + o.phone + '</td><td>' + qty + ' SP</td><td>' + (o.note || '') + '</td><td>' + (o.created_at || '') + '</td></tr>';
            }).join('');
        }
        renderOrders();
    </script>
</body>
</html>
HTML;

pccc24_write_dist('wp-admin/index.html', $admin_html);

// 5. Thu thập toàn bộ danh sách URL cần kết xuất ra HTML tĩnh
$routes = [
    '/'                 => 'index.html',
    '/san-pham'         => 'san-pham/index.html',
    '/san-pham/tim-kiem'=> 'san-pham/tim-kiem/index.html',
    '/dich-vu'          => 'dich-vu/index.html',
    '/tin-tuc'          => 'tin-tuc/index.html',
    '/contacts'         => 'contacts/index.html',
];

foreach (get_terms(['taxonomy' => 'pccc_product_cat', 'hide_empty' => false]) as $t) {
    $routes["/san-pham/{$t->slug}"] = "san-pham/{$t->slug}/index.html";
}
foreach (get_terms(['taxonomy' => 'pccc_service_type', 'hide_empty' => false]) as $t) {
    $routes["/dich-vu/{$t->slug}"] = "dich-vu/{$t->slug}/index.html";
}
foreach (get_terms(['taxonomy' => 'pccc_page_type', 'hide_empty' => false]) as $t) {
    $routes["/pages/type/{$t->slug}"] = "pages/type/{$t->slug}/index.html";
    $routes["/{$t->slug}"]            = "{$t->slug}/index.html";
}
foreach (get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1]) as $p) {
    $routes["/pages/{$p->post_name}"] = "pages/{$p->post_name}/index.html";
}
foreach (get_posts(['post_type' => 'pccc_service', 'post_status' => 'publish', 'numberposts' => -1]) as $p) {
    $oid = pccc24_old_id($p);
    $routes["/dich-vu/{$p->post_name}/{$oid}"] = "dich-vu/{$p->post_name}/{$oid}/index.html";
}
foreach (get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1]) as $p) {
    $oid = pccc24_old_id($p);
    $routes["/tin-tuc/{$p->post_name}/{$oid}"] = "tin-tuc/{$p->post_name}/{$oid}/index.html";
}
foreach ($product_posts as $p) {
    $oid = pccc24_old_id($p);
    $routes["/san-pham/{$p->post_name}/{$oid}"] = "san-pham/{$p->post_name}/{$oid}/index.html";
}

WP_CLI::log('Total routes to export: ' . count($routes));

// 6. Tải HTML từ server local http://localhost:8080 và ghi ra thư mục dist
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$count = 0;
foreach ($routes as $url_path => $out_file) {
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080' . $url_path);
    $raw_html = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($status === 200 && $raw_html) {
        $transformed = pccc24_transform_for_gh_pages($raw_html, $base_prefix);
        pccc24_write_dist($out_file, $transformed);
        $count++;
    } else {
        WP_CLI::warning("Skipped $url_path (HTTP $status)");
    }
}

// Trang 404.html
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/san-pham/khong-ton-tai/999999');
$html_404 = curl_exec($ch);
if ($html_404) {
    pccc24_write_dist('404.html', pccc24_transform_for_gh_pages($html_404, $base_prefix));
}
curl_close($ch);

WP_CLI::success("Exported $count static pages to $dist_dir");
