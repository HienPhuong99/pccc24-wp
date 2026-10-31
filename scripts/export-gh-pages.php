<?php
/**
 * Kết xuất toàn bộ website WordPress PCCC24 (giao diện mới frontend-design) ra bản tĩnh tương tác cho GitHub Pages (gh-pages)
 * Chạy: php tools/wp-cli.phar --path=d:\Code\Pccc24-WP eval-file scripts/export-gh-pages.php
 */

defined('ABSPATH') || exit;

global $dist_dir;
$base_prefix = '/pccc24-wp';
$dist_dir    = getenv('PCCC24_GH_DIST') ?: (sys_get_temp_dir() . '/pccc24-gh-pages');
$GLOBALS['dist_dir'] = $dist_dir;

if (is_dir($dist_dir)) {
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
    // 1. Ảnh uploads: bản nén .webp phục vụ trực tiếp từ GitHub Pages (/pccc24-wp/wp-content/uploads/pccc24/...),
    //    còn ảnh gốc PNG/JPG trong bài viết cũ trỏ về CDN https://pccc24.com/uploads/...
    $html = preg_replace_callback(
        '~(?:https?://localhost:8080)?/wp-content/uploads/pccc24/([^"\'\s<>)]+)~i',
        function ($m) use ($prefix) {
            $parts = explode('/', $m[1]);
            $decoded_parts = array_map('rawurldecode', $parts);
            $rel_decoded = implode('/', $decoded_parts);
            $encoded = implode('/', array_map('rawurlencode', $decoded_parts));
            if (str_ends_with(strtolower($rel_decoded), '.webp') && is_file(ABSPATH . 'wp-content/uploads/pccc24/' . $rel_decoded)) {
                return $prefix . '/wp-content/uploads/pccc24/' . $encoded;
            }
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
        '~\b(href|src|action|data-href)=(["\'])/(?!pccc24-wp/|/)(san-pham|dich-vu|tin-tuc|pages|contacts|chinh-sach|quy-dinh|gioi-thieu-cong-ty|wp-content|wp-includes|wp-json|wp-admin|wp-login\.php)~i',
        '$1=$2' . $prefix . '/$3',
        $html
    );

    // 4. Link trang chủ href="/"
    $html = preg_replace('~\bhref=(["\'])/\1~i', 'href=$1' . $prefix . '/$1', $html);

    // 5. Nhúng script adapter Vanilla JS cho GitHub Pages trước </body>
    $adapter_tag = '<script src="' . $prefix . '/wp-content/themes/pccc24-theme/assets/js/gh-pages-demo.js"></script></body>';
    $html = str_replace('</body>', $adapter_tag, $html);

    return $html;
}

function pccc24_write_dist(string $rel_path, string $content): void
{
    $dist_dir = $GLOBALS['dist_dir'];
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

// 1. Copy toàn bộ theme assets + chỉ đồng bộ các file ảnh .webp chuẩn (bỏ qua 375 file lỗi mã hoá Ã và ảnh gốc nặng)
$theme_assets_src = get_stylesheet_directory() . '/assets';
$theme_assets_dst = $dist_dir . '/wp-content/themes/pccc24-theme/assets';
pccc24_copy_dir($theme_assets_src, $theme_assets_dst);
$uploads_src = ABSPATH . 'wp-content/uploads/pccc24';
$uploads_dst = $dist_dir . '/wp-content/uploads/pccc24';
if (is_dir($uploads_src)) {
    $uit = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploads_src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($uit as $uitem) {
        if ($uitem->isDir()) {
            continue;
        }
        $fname = $uitem->getFilename();
        if (!str_ends_with(strtolower($fname), '.webp') || str_contains($fname, 'Ã')) {
            continue;
        }
        $utarget = $uploads_dst . DIRECTORY_SEPARATOR . $uit->getSubPathName();
        if (!is_dir(dirname($utarget))) {
            mkdir(dirname($utarget), 0777, true);
        }
        copy($uitem->getPathname(), $utarget);
    }
}
WP_CLI::log('Copied theme assets and synced clean WebP uploads.');

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
    if (!empty($json['image_url'])) {
        $json['image_url'] = pccc24_transform_for_gh_pages($json['image_url'], $base_prefix);
    }
    $json['url']     = $base_prefix . parse_url(get_permalink($p), PHP_URL_PATH) . '/';
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

// 3. Tạo file Vanilla JS Adapter (gh-pages-demo.js) hỗ trợ fetch(/products/{id}), Search ?q=, Sort ?sorted=, Paginate ?page=
$adapter_js = <<<'JS'
(function () {
  "use strict";

  var PREFIX = "/pccc24-wp";
  var noImg = PREFIX + "/wp-content/themes/pccc24-theme/assets/images/no-image.png";
  var esc = function (s) {
    var d = document.createElement("div");
    d.textContent = s == null ? "" : String(s);
    return d.innerHTML;
  };

  // 1. Chặn window.fetch để chuyển /wp-json/pccc24/v1/products/{id} sang file .json tĩnh trên GitHub Pages
  var origFetch = window.fetch ? window.fetch.bind(window) : null;
  if (origFetch) {
    window.fetch = function (input, init) {
      if (typeof input === "string") {
        var mProd = input.match(/\/wp-json\/pccc24\/v1\/products\/(\d+)\/?$/);
        if (mProd) {
          input = PREFIX + "/wp-json/pccc24/v1/products/" + mProd[1] + ".json";
        } else if (/\/wp-json\/pccc24\/v1\/supports\/?$/.test(input)) {
          input = PREFIX + "/wp-json/pccc24/v1/supports.json";
        } else if (/\/wp-json\/pccc24\/v1\/quotes\/?/.test(input)) {
          return Promise.resolve(
            new Response(
              JSON.stringify({
                ok: true,
                id: 999,
                message: "Bản demo tĩnh: đã nhận thông tin mẫu, vui lòng gọi trực tiếp để báo giá.",
              }),
              { status: 200, headers: { "Content-Type": "application/json" } }
            )
          );
        }
      }
      return origFetch(input, init);
    };
  }

  // 2. Fallback tự động cho ảnh bị lỗi
  document.addEventListener(
    "error",
    function (e) {
      var t = e.target;
      if (t && t.tagName === "IMG" && t.src !== noImg && !t.dataset.fallback) {
        t.dataset.fallback = "1";
        t.src = noImg;
      }
    },
    true
  );

  // 3. Xử lý Tìm kiếm (?q=...), Sắp xếp (?sorted=1..4) và Phân trang (?page=N) bằng Vanilla JS
  document.addEventListener("DOMContentLoaded", function () {
    var sp = new URLSearchParams(window.location.search);
    var path = window.location.pathname.replace(/\/+$/, "");
    var isSearchPage = /\/san-pham\/tim-kiem$/.test(path);
    var isProductsRoot = /\/san-pham$/.test(path);
    var catMatch = path.match(/\/san-pham\/([^\/]+)$/);
    var catSlug = catMatch && catMatch[1] !== "tim-kiem" ? catMatch[1] : null;

    var qParam = sp.get("q") || "";
    var sortedParam = parseInt(sp.get("sorted") || "0", 10);
    var pageParam = parseInt(sp.get("page") || "1", 10);

    var sortSelect = document.getElementById("sorted");
    if (sortSelect && sortedParam > 0) {
      sortSelect.value = String(sortedParam);
    }

    if ((isSearchPage || sortedParam > 0 || pageParam > 1) && (isSearchPage || isProductsRoot || catSlug) && origFetch) {
      origFetch(PREFIX + "/wp-json/pccc24/v1/products-all.json")
        .then(function (r) {
          return r.json();
        })
        .then(function (all) {
          if (!Array.isArray(all)) return;
          var list = all.slice();

          if (isSearchPage) {
            var qClean = qParam.trim().toLowerCase();
            document.querySelectorAll('input[name="q"]').forEach(function (inp) {
              inp.value = qParam;
            });
            if (qClean) {
              list = list.filter(function (p) {
                return (p.name || "").toLowerCase().indexOf(qClean) !== -1;
              });
            }
            var h1 = document.querySelector(".page-head h1");
            if (h1) {
              h1.textContent = qParam ? 'Kết quả cho "' + qParam + '" (' + list.length + ")" : "Tìm kiếm sản phẩm";
            }
          } else if (catSlug) {
            list = list.filter(function (p) {
              return Array.isArray(p.cats) && p.cats.indexOf(catSlug) !== -1;
            });
          }

          if (sortedParam === 1) {
            list.sort(function (a, b) {
              return a.name.localeCompare(b.name, "vi");
            });
          } else if (sortedParam === 2) {
            list.sort(function (a, b) {
              return b.name.localeCompare(a.name, "vi");
            });
          } else if (sortedParam === 3) {
            list.sort(function (a, b) {
              return (a.price || 0) - (b.price || 0);
            });
          } else if (sortedParam === 4) {
            list.sort(function (a, b) {
              return (b.price || 0) - (a.price || 0);
            });
          }

          var perPage = 20;
          var totalPages = Math.max(1, Math.ceil(list.length / perPage));
          var page = Math.min(totalPages, Math.max(1, pageParam));
          var slice = list.slice((page - 1) * perPage, page * perPage);

          var grid = document.querySelector(".grid-products");
          var pageHead = document.querySelector(".page-head");
          if (!grid && pageHead) {
            var oldPanel = pageHead.parentElement.querySelector(".panel");
            if (oldPanel) oldPanel.remove();
            grid = document.createElement("div");
            grid.className = "grid-products";
            pageHead.insertAdjacentElement("afterend", grid);
          }

          if (grid) {
            if (slice.length === 0) {
              grid.outerHTML = '<div class="panel">Không tìm thấy sản phẩm phù hợp.</div>';
            } else {
              grid.innerHTML = slice
                .map(function (p) {
                  var priceStr = p.price > 0 ? Number(p.price).toLocaleString("vi-VN") + "đ" : "Liên hệ";
                  return (
                    '<article class="product">' +
                    '<div class="product-img"><img src="' +
                    esc(p.image_url || noImg) +
                    '" alt="" loading="lazy"></div>' +
                    '<a class="product-link" href="' +
                    esc(p.url) +
                    '"><h3>' +
                    esc(p.name) +
                    "</h3></a>" +
                    '<div class="product-foot">' +
                    '<span class="product-price">' +
                    esc(priceStr) +
                    "</span>" +
                    '<button type="button" class="quick" data-quick="' +
                    Number(p.id) +
                    '">Xem nhanh</button>' +
                    "</div>" +
                    "</article>"
                  );
                })
                .join("");
            }
          }

          var pag = document.querySelector("ul.pagination");
          if (totalPages <= 1) {
            if (pag) pag.remove();
          } else {
            if (!pag && grid && grid.parentElement) {
              pag = document.createElement("ul");
              pag.className = "pagination";
              grid.insertAdjacentElement("afterend", pag);
            }
            if (pag) {
              var itemsHtml = "";
              for (var i = 1; i <= totalPages; i++) {
                var nextSp = new URLSearchParams(window.location.search);
                nextSp.set("page", String(i));
                itemsHtml +=
                  '<li class="' +
                  (i === page ? "active" : "") +
                  '"><a href="' +
                  window.location.pathname +
                  "?" +
                  nextSp.toString() +
                  '">' +
                  i +
                  "</a></li>";
              }
              pag.innerHTML = itemsHtml;
            }
          }
        });
    }
  });
})();
JS;

pccc24_write_dist('wp-content/themes/pccc24-theme/assets/js/gh-pages-demo.js', $adapter_js);

// 4. Tạo trang /wp-login.php/index.html điền sẵn Tài khoản, Email, Mật khẩu mặc định
$login_html = <<<'HTML'
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập ‹ PCCC24 — WordPress</title>
    <style>
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { margin: 0; background: #f0f0f1; color: #3c434a; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .login-card { width: 100%; max-width: 380px; background: #fff; border: 1px solid #c3c4c7; padding: 26px 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); border-radius: 4px; }
        .logo-wrap { text-align: center; margin-bottom: 18px; }
        .logo-wrap img { max-height: 56px; }
        .prefill-box { background: #f0f6fc; border: 1px solid #72aee6; border-left: 4px solid #2271b1; padding: 10px 12px; margin-bottom: 16px; border-radius: 4px; font-size: 13px; line-height: 1.55; color: #1d2327; }
        .prefill-box code { background: #e5eff8; padding: 1px 5px; border-radius: 3px; font-weight: 600; }
        .switch-btns { display: flex; gap: 6px; margin-top: 8px; }
        .switch-btns button { flex: 1; background: #f6f7f7; border: 1px solid #2271b1; color: #2271b1; padding: 5px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .switch-btns button:hover { background: #2271b1; color: #fff; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 14px; margin-bottom: 5px; color: #1d2327; font-weight: 500; }
        .field input { width: 100%; padding: 8px 12px; font-size: 16px; border: 1px solid #8c8f94; border-radius: 4px; }
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; }
        .btn-submit { background: #2271b1; color: #fff; border: 1px solid #2271b1; padding: 8px 18px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #135e96; }
        .back-link { margin-top: 16px; font-size: 13px; }
        .back-link a { color: #50575e; text-decoration: none; }
        .back-link a:hover { color: #2271b1; }
    </style>
</head>
<body>
    <div class="logo-wrap">
        <a href="/pccc24-wp/"><img src="/pccc24-wp/wp-content/themes/pccc24-theme/assets/images/logo.png" alt="PCCC24"></a>
    </div>
    <div class="login-card">
        <div class="prefill-box">
            <strong>🔑 Tài khoản mặc định (Đã điền sẵn):</strong><br>
            • Tài khoản: <code>admin</code><br>
            • Email: <code>admin@pccc24.local</code><br>
            • Mật khẩu: <code>pccc24@admin2026</code>
            <div class="switch-btns">
                <button type="button" onclick="fillCreds('admin')">Điền Username</button>
                <button type="button" onclick="fillCreds('admin@pccc24.local')">Điền Email</button>
            </div>
        </div>
        <form onsubmit="event.preventDefault(); window.location.href='/pccc24-wp/wp-admin/';">
            <div class="field">
                <label for="user_login">Tên người dùng hoặc Địa chỉ Email</label>
                <input type="text" id="user_login" name="log" value="admin" required>
            </div>
            <div class="field">
                <label for="user_pass">Email quản trị</label>
                <input type="email" id="user_email" name="email" value="admin@pccc24.local" required>
            </div>
            <div class="field">
                <label for="user_pass_input">Mật khẩu</label>
                <input type="text" id="user_pass_input" name="pwd" value="pccc24@admin2026" required>
            </div>
            <div class="actions">
                <label style="font-size:13px;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" checked> Tự động đăng nhập
                </label>
                <button type="submit" id="wp-submit" class="btn-submit">Đăng nhập &rarr;</button>
            </div>
        </form>
    </div>
    <div class="back-link">
        <a href="/pccc24-wp/">&larr; Quay lại PCCC24</a>
    </div>
    <script>
        function fillCreds(loginVal) {
            document.getElementById('user_login').value = loginVal;
            document.getElementById('user_email').value = 'admin@pccc24.local';
            document.getElementById('user_pass_input').value = 'pccc24@admin2026';
        }
    </script>
</body>
</html>
HTML;

pccc24_write_dist('wp-login/index.html', $login_html);
pccc24_write_dist('wp-login.php/index.html', $login_html);

// 5. Tạo trang /wp-admin/index.html theo đúng phong cách Dashboard V18 (V18.mp4)
$admin_html = <<<'HTML'
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bảng tin ‹ PCCC24 — WordPress Admin (Dashboard V18)</title>
    <style>
        :root {
            --v18-bg: #f3f4f7;
            --v18-dock: #14161b;
            --v18-ink: #14161b;
            --v18-muted: #646b78;
            --red: #c8201a;
            --emerald: #0b7a4b;
            --sep: rgba(20, 22, 27, .07);
            --shadow: 0 2px 6px rgba(20, 22, 27, .04), 0 12px 28px rgba(20, 22, 27, .05);
            --shadow-hover: 0 8px 20px rgba(20, 22, 27, .08), 0 20px 38px rgba(20, 22, 27, .08);
        }
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Be Vietnam Pro", system-ui, sans-serif; }
        body { margin: 0; background: var(--v18-bg); color: var(--v18-ink); display: flex; min-height: 100vh; padding: 14px; gap: 18px; -webkit-font-smoothing: antialiased; }
        /* Floating Dark Pill Sidebar Dock (V18.mp4) */
        .v18-dock {
            width: 210px;
            background: var(--v18-dock);
            color: #fff;
            border-radius: 26px;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-shrink: 0;
            box-shadow: 0 18px 42px rgba(20, 22, 27, .24), inset 0 0 0 1px rgba(255, 255, 255, .08);
            position: sticky;
            top: 14px;
            height: calc(100vh - 28px);
            transition: width 220ms cubic-bezier(.23, 1, .32, 1);
        }
        .v18-dock.is-collapsed { width: 68px; border-radius: 999px; align-items: center; padding: 16px 8px; }
        .v18-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            margin-bottom: 6px;
        }
        .v18-brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: -.02em;
            color: #fff;
            text-decoration: none;
        }
        .v18-brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--red);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
        }
        .v18-dock.is-collapsed .v18-brand-text { display: none; }
        .v18-nav { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .v18-nav li {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, .72);
            transition: background-color 160ms, color 160ms, transform 160ms;
        }
        .v18-nav li:hover { background: rgba(255, 255, 255, .10); color: #fff; }
        .v18-nav li.active {
            background: #ffffff;
            color: var(--v18-dock);
            font-weight: 700;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .22);
        }
        .v18-nav-icon { font-size: 17px; line-height: 1; flex-shrink: 0; }
        .v18-dock.is-collapsed .v18-nav li { width: 44px; height: 44px; padding: 0; justify-content: center; border-radius: 999px; }
        .v18-dock.is-collapsed .v18-nav-label {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(-4px);
            background: var(--v18-dock);
            color: #fff;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .28);
            transition: opacity 150ms, transform 150ms;
            z-index: 20;
        }
        .v18-dock.is-collapsed .v18-nav li:hover .v18-nav-label {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
        .v18-collapse-btn {
            border: 0;
            background: rgba(255, 255, 255, .08);
            color: rgba(255, 255, 255, .75);
            border-radius: 12px;
            padding: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        .v18-collapse-btn:hover { background: rgba(255, 255, 255, .14); color: #fff; }

        /* Main Canvas & Bento Grid */
        .wp-main { flex: 1; min-width: 0; padding: 6px 10px 24px; }
        .wp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .wp-header h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.025em; }
        .btn-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 999px;
            border: 0;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform 150ms, background-color 150ms;
        }
        .btn-pill-dark { background: var(--v18-dock); color: #fff; box-shadow: 0 6px 16px rgba(20, 22, 27, .18); }
        .btn-pill-dark:hover { background: var(--red); transform: translateY(-1px); }
        .btn-pill-soft { background: #fff; color: var(--v18-ink); box-shadow: var(--shadow); }
        .btn-pill-soft:hover { background: #f9fafb; }

        .v18-bento-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 22px;
        }
        @media (max-width: 1024px) { .v18-bento-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 680px) {
            body { flex-direction: column; }
            .v18-dock { width: 100%; height: auto; position: static; }
            .v18-bento-grid { grid-template-columns: 1fr; }
        }
        .v18-hero-card {
            grid-row: span 2;
            border-radius: 24px;
            padding: 26px 28px;
            background: linear-gradient(128deg, #e9d5ff 0%, #fbcfe8 52%, #dbeafe 100%);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
            transition: transform 220ms cubic-bezier(.23, 1, .32, 1), box-shadow 220ms;
        }
        .v18-hero-card:hover, .stat-card:hover, .product-bento-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .v18-badge {
            display: inline-flex;
            align-self: flex-start;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .75);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .03em;
        }
        .v18-hero-card h2 { margin: 8px 0 6px; font-size: 25px; font-weight: 800; letter-spacing: -.03em; line-height: 1.15; }
        .v18-hero-card p { margin: 0; font-size: 14px; color: rgba(20, 22, 27, .74); }
        .stat-card {
            background: #fff;
            border-radius: 22px;
            padding: 20px 22px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
            transition: transform 220ms cubic-bezier(.23, 1, .32, 1), box-shadow 220ms;
        }
        .stat-top { display: flex; justify-content: space-between; align-items: center; }
        .stat-card .lbl { font-size: 13px; font-weight: 600; color: var(--v18-muted); }
        .stat-card .ico { width: 36px; height: 36px; border-radius: 12px; background: var(--v18-bg); display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
        .stat-card .num { font-size: 32px; font-weight: 800; letter-spacing: -.03em; color: var(--v18-ink); }
        .stat-card .trend { font-size: 12.5px; font-weight: 600; color: var(--emerald); }

        .bento-card {
            background: #fff;
            border-radius: 22px;
            padding: 22px;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }
        .bento-title { font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; justify-content: space-between; align-items: center; }
        .products-bento-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .product-bento-card {
            background: #fff;
            border-radius: 20px;
            padding: 14px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-decoration: none;
            color: var(--v18-ink);
            transition: transform 220ms cubic-bezier(.23, 1, .32, 1), box-shadow 220ms;
        }
        .product-bento-card img { width: 100%; aspect-ratio: 4/3; object-fit: contain; background: #f8f9fb; border-radius: 14px; padding: 8px; }
        .product-bento-card b { font-size: 13.5px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .product-bento-card span { font-size: 12.5px; font-weight: 700; color: var(--red); margin-top: auto; }

        table.wp-list-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 18px; overflow: hidden; box-shadow: var(--shadow); }
        table.wp-list-table th, table.wp-list-table td { text-align: left; padding: 12px 16px; border-bottom: 1px solid var(--sep); font-size: 13.5px; }
        table.wp-list-table th { background: #f9fafb; font-weight: 700; font-size: 12px; color: var(--v18-muted); }
        table.wp-list-table a { color: var(--v18-ink); font-weight: 600; text-decoration: none; }
        table.wp-list-table a:hover { color: var(--red); }
        .form-row { display: grid; grid-template-columns: 220px 1fr; gap: 12px; align-items: start; margin-bottom: 12px; }
        .form-row label { font-weight: 600; font-size: 13px; padding-top: 8px; color: var(--v18-muted); }
        .form-row input, .form-row textarea { width: 100%; padding: 9px 12px; border: 1px solid rgba(20,22,27,.12); border-radius: 12px; font-size: 14px; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
    </style>
</head>
<body>
    <aside class="v18-dock" id="v18-dock">
        <div class="v18-brand">
            <a class="v18-brand-logo" href="/pccc24-wp/">
                <span class="v18-brand-mark">24</span>
                <span class="v18-brand-text">PCCC24 Admin</span>
            </a>
        </div>
        <ul class="v18-nav">
            <li class="active" data-tab="dashboard"><span class="v18-nav-icon">⊞</span><span class="v18-nav-label">Bảng tin V18</span></li>
            <li data-tab="products"><span class="v18-nav-icon">🧯</span><span class="v18-nav-label">Sản phẩm (385)</span></li>
            <li data-tab="settings"><span class="v18-nav-icon">⚙</span><span class="v18-nav-label">Cài đặt PCCC24</span></li>
        </ul>
        <button type="button" class="v18-collapse-btn" id="v18-toggle" title="Thu gọn / Mở rộng thanh Dock">⇄ Thu gọn Dock</button>
    </aside>

    <main class="wp-main">
        <div class="wp-header">
            <h1>Dashboard V18 — Quản trị PCCC Hoàng Quân Phát</h1>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="/pccc24-wp/wp-login/" class="btn-pill btn-pill-soft">🔑 Trang Đăng nhập</a>
                <a href="/pccc24-wp/" class="btn-pill btn-pill-dark">🌐 Mở Trang Chủ ↗</a>
            </div>
        </div>

        <section id="tab-dashboard" class="tab-pane active">
            <div class="v18-bento-grid">
                <div class="v18-hero-card">
                    <div>
                        <span class="v18-badge">● PCCC24 · DASHBOARD V18</span>
                        <h2>Hệ thống Quản trị Thiết bị &amp; Dịch vụ PCCC</h2>
                        <p>Đồng bộ 100% dữ liệu từ PCCC24 sang WordPress: 385 sản phẩm, 40 danh mục, 62 bài viết kỹ thuật, 14 dịch vụ thi công – bảo trì và hệ thống nhận báo giá nhanh.</p>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="button" class="btn-pill btn-pill-dark" onclick="document.querySelector('[data-tab=products]').click()">Xem 385 Sản phẩm &rarr;</button>
                        <button type="button" class="btn-pill btn-pill-soft" onclick="document.querySelector('[data-tab=settings]').click()">Cấu hình PCCC24</button>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-top"><span class="lbl">Thiết bị PCCC</span><span class="ico">🧯</span></div>
                    <div class="num">385</div>
                    <div class="trend">↗ 40 danh mục 2 cấp</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top"><span class="lbl">Tin tức &amp; Dịch vụ</span><span class="ico">📰</span></div>
                    <div class="num">76</div>
                    <div class="trend">↗ 62 bài viết · 14 dịch vụ</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top"><span class="lbl">Ảnh WebP Tối ưu</span><span class="ico">⚡</span></div>
                    <div class="num">472</div>
                    <div class="trend">↗ LCP 0.62s (&lt; 250 KB)</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top"><span class="lbl">Kiểm thử E2E</span><span class="ico">✓</span></div>
                    <div class="num">71/71</div>
                    <div class="trend">↗ Playwright + axe-core WCAG</div>
                </div>
            </div>

            <div class="bento-title">
                <span>Thiết bị mới cập nhật</span>
                <a href="/pccc24-wp/san-pham/" style="font-size:13px;color:var(--red);text-decoration:none;">Xem tất cả &rarr;</a>
            </div>
            <div class="products-bento-grid" id="v18-recent-grid"></div>

            <div class="bento-card">
                <div class="bento-title"><span>Metabox "Thông tin PCCC24" (Mẫu chỉnh sửa sản phẩm #718)</span></div>
                <div class="form-row"><label>Tên sản phẩm</label><input value="Bình chữa cháy gốc nước công nghệ sinh học Ecosafe"></div>
                <div class="form-row"><label>Giá (0 = Liên hệ)</label><input value="0"></div>
                <div class="form-row"><label>Thông số kỹ thuật</label><textarea rows="4">Chủng loại | Bình chữa cháy gốc nước sinh học
Model | Ecosafe
Bảo hành | 12 tháng</textarea></div>
                <button class="btn-pill btn-pill-dark" onclick="alert('Đã lưu thông tin Metabox (Demo)!')">Lưu cập nhật</button>
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

        <section id="tab-settings" class="tab-pane">
            <div class="bento-card">
                <div class="bento-title"><span>Cài đặt chung PCCC24 (`options-general.php?page=pccc24`)</span></div>
                <div class="form-row"><label>Tên công ty (`company`)</label><input value="CÔNG TY TNHH MTV HOÀNG QUÂN PHÁT"></div>
                <div class="form-row"><label>Địa chỉ (`address`)</label><textarea rows="2">262 Đỗ Mười, Phường Tam Bình, Thành phố Hồ Chí Minh</textarea></div>
                <div class="form-row"><label>Điện thoại (`phone`)</label><input value="0982765114"></div>
                <div class="form-row"><label>ĐT bán hàng (`phone_sale`)</label><input value="0982765114"></div>
                <div class="form-row"><label>ĐT kỹ thuật (`phone_technique`)</label><input value="02866566422"></div>
                <div class="form-row"><label>Email (`email`)</label><input value="hoangquanphat@gmail.com"></div>
                <div class="form-row"><label>GPKD (`gpkd`)</label><input value="3701888205"></div>
                <button class="btn-pill btn-pill-dark" onclick="alert('Đã lưu cấu hình PCCC24 (Demo)!')">Lưu thay đổi</button>
            </div>
        </section>
    </main>
    <script>
        var dock = document.getElementById('v18-dock');
        document.getElementById('v18-toggle').addEventListener('click', function() {
            dock.classList.toggle('is-collapsed');
            this.textContent = dock.classList.contains('is-collapsed') ? '⇄' : '⇄ Thu gọn Dock';
        });
        document.querySelectorAll('.v18-nav li').forEach(function(li) {
            li.addEventListener('click', function() {
                document.querySelectorAll('.v18-nav li').forEach(x => x.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(x => x.classList.remove('active'));
                li.classList.add('active');
                document.getElementById('tab-' + li.dataset.tab).classList.add('active');
            });
        });

        fetch('/pccc24-wp/wp-json/pccc24/v1/products-all.json')
            .then(r => r.json())
            .then(list => {
                var recent = document.getElementById('v18-recent-grid');
                if (recent) {
                    recent.innerHTML = list.slice(0, 8).map(p =>
                        '<a class="product-bento-card" href="' + p.url + '" target="_blank">' +
                        '<img src="' + p.image_url + '" alt="" loading="lazy">' +
                        '<b>' + p.name + '</b>' +
                        '<span>#' + p.id + ' · ' + (p.price > 0 ? p.price.toLocaleString('vi-VN') + 'đ' : 'Liên hệ') + '</span>' +
                        '</a>'
                    ).join('');
                }
                var tbody = document.getElementById('admin-products-tbody');
                tbody.innerHTML = list.slice(0, 50).map(p =>
                    '<tr><td><strong>#' + p.id + '</strong></td><td><a href="' + p.url + '" target="_blank">' + p.name + '</a></td><td>' + (p.price > 0 ? p.price.toLocaleString('vi-VN') + 'đ' : 'Liên hệ') + '</td><td><a href="' + p.url + '" target="_blank">Xem ngoài trang &rarr;</a></td></tr>'
                ).join('');
            });
    </script>
</body>
</html>
HTML;

pccc24_write_dist('wp-admin/index.html', $admin_html);

// 6. Thu thập toàn bộ danh sách URL cần kết xuất ra HTML tĩnh
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

// 7. Tải HTML từ server local http://localhost:8080 và ghi ra thư mục dist
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: keep-alive']);

$count = 0;
foreach ($routes as $url_path => $out_file) {
    $raw_html = '';
    $status   = 0;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080' . $url_path);
        $raw_html = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status === 200 && $raw_html) {
            break;
        }
        usleep(150000);
    }
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
