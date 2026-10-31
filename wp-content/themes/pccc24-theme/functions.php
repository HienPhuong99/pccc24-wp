<?php
/**
 * PCCC24 Theme Functions & Helpers
 */

defined('ABSPATH') || exit;

add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
});

// Tắt thanh Admin Bar ngoài frontend để giao diện khớp 100% với bản gốc khi kiểm thử
add_filter('show_admin_bar', '__return_false');

/**
 * Đường dẫn tới file tài nguyên trong wp-content/themes/pccc24-theme/assets/
 */
function pccc24_theme_asset(string $path = ''): string
{
    return get_stylesheet_directory_uri() . '/assets/' . ltrim($path, '/');
}

function pccc24_no_image_url(): string
{
    return pccc24_theme_asset('images/no-image.png');
}

/**
 * Lấy URL ảnh đại diện của post (ưu tiên bản nén nhẹ -card.webp / -hero.webp / -detail.webp nếu có)
 */
function pccc24_post_image_url($post = null, string $variant = 'card'): string
{
    $post = get_post($post);
    if (!$post) {
        return pccc24_no_image_url();
    }
    $url = get_the_post_thumbnail_url($post, 'full');
    if (!$url) {
        return pccc24_no_image_url();
    }
    $thumb_id = (int) get_post_thumbnail_id($post->ID);
    if ($thumb_id && ($file = get_attached_file($thumb_id))) {
        $suffix = $post->post_type === 'pccc_slider' || $variant === 'hero'
            ? 'hero'
            : ($variant === 'detail' ? 'detail' : 'card');
        $webp_file = preg_replace('/\.[^.]+$/', '-' . $suffix . '.webp', $file);
        if ($webp_file && is_file($webp_file)) {
            return preg_replace('/\.[^.]+$/', '-' . $suffix . '.webp', $url);
        }
    }
    return $url;
}

/**
 * Lấy giá trị cấu hình từ pccc24_settings (tương đương getSettingKey() bên CI4)
 */
function pccc24_get_setting(string $key, bool $escape = true): string
{
    $defaults = [
        'site_title'       => 'Thiết bị phòng cháy chữa cháy - Tư vấn thiết kế thi công lắp đặt hệ thống phòng cháy chữa cháy',
        'site_description' => 'Cung cấp sỉ lẻ các loại bình chữa cháy, phụ kiện PCCC, thi công lắp đặt hệ thống PCCC, bảo trì pccc, nạp sạc bình chữa cháy giá rẻ',
        'company'          => 'CÔNG TY TNHH MTV HOÀNG QUÂN PHÁT',
        'address'          => '262 Đỗ Mười, Phường Tam Bình, Thành phố Hồ Chí Minh <p>Trụ sở chính: Số 16, Đường N4, khu nhà ở TM DV Phú Mỹ, khu 5, phường Bình Dương, Thành phố Hồ Chí Minh</p>',
        'phone'            => '0982765114',
        'phone_sale'       => '0982765114',
        'phone_technique'  => '02866566422',
        'email'            => 'hoangquanphat@gmail.com',
        'gpkd'             => '3701888205',
        'facebook'         => 'https://www.facebook.com/pccc24',
        'twitter'          => 'https://twitter.com/pccc24',
        'youtube'          => 'https://www.youtube.com/c/Pccc24HQP',
        'tiktok'           => 'https://www.tiktok.com/',
        'zalo'             => '0982765114',
        'logo'             => pccc24_theme_asset('images/logo.png'),
        'logo_seo'         => pccc24_theme_asset('images/logo.png'),
    ];

    $val = function_exists('pccc24_setting') ? (string) pccc24_setting($key) : '';
    if ($val === '') {
        $val = $defaults[$key] ?? '';
    }

    // Chuyển URL ảnh logo từ pccc24.com/uploads/... sang đường dẫn local wp-content/uploads/pccc24/...
    if (in_array($key, ['logo', 'logo_seo'], true) && $val !== '') {
        if (preg_match('~^https?://(?:www\.|img\.)?pccc24\.com/uploads/(.+)$~i', $val, $m)) {
            $rel = $m[1];
            $upload_dir = wp_upload_dir();
            if (is_file($upload_dir['basedir'] . '/pccc24/' . $rel)) {
                $val = $upload_dir['baseurl'] . '/pccc24/' . $rel;
            } else {
                $val = pccc24_theme_asset('images/logo.png');
            }
        }
    }

    if ($escape && !in_array($key, ['logo', 'logo_seo', 'facebook', 'twitter', 'youtube', 'tiktok'], true)) {
        return esc_html($val);
    }

    return $val;
}

/** Link tel: từ một khoá setting số điện thoại. */
function pccc24_tel(string $key = 'phone_sale'): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', pccc24_get_setting($key, false));
}

function pccc24_zalo_url(): string
{
    return 'https://zalo.me/' . preg_replace('/[^0-9]/', '', pccc24_get_setting('zalo', false));
}

/** Vài icon nội tuyến (thay cho boxicons). */
function pccc24_icon(string $name): string
{
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'menu'   => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'phone'  => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'chat'   => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.4A8 8 0 1 1 21 12z"/>',
        'right'  => '<path d="m9 6 6 6-6 6"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

/**
 * Định dạng tiền tệ chuẩn PCCC24 (tương đương priceFormat() bên CI4)
 */
function pccc24_price_format($price): string
{
    $num = (float) $price;
    return number_format($num, 0, ',', '.');
}

/**
 * Định dạng ngày tháng (tương đương timeString() bên CI4)
 */
function pccc24_time_string($post = null, bool $with_time = false): string
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }
    return get_the_date($with_time ? 'H:i:s d/m/Y' : 'd/m/Y', $post);
}

/**
 * Lấy cây Danh mục sản phẩm 2 cấp (tương đương getInitCategory() bên CI4)
 */
function pccc24_get_category_tree(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $terms = get_terms([
        'taxonomy'   => 'pccc_product_cat',
        'hide_empty' => false,
    ]);
    if (is_wp_error($terms) || empty($terms)) {
        return $cached = [];
    }

    usort($terms, function ($a, $b) {
        $sa = (int) get_term_meta($a->term_id, '_pccc_sorted', true);
        $sb = (int) get_term_meta($b->term_id, '_pccc_sorted', true);
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        $oa = (int) get_term_meta($a->term_id, '_pccc_old_id', true);
        $ob = (int) get_term_meta($b->term_id, '_pccc_old_id', true);
        return $oa <=> $ob;
    });

    $roots = [];
    foreach ($terms as $t) {
        if ((int) $t->parent === 0) {
            $children = [];
            foreach ($terms as $c) {
                if ((int) $c->parent === (int) $t->term_id) {
                    $c->url = get_term_link($c);
                    $children[] = $c;
                }
            }
            $t->childrens = $children;
            $t->url = get_term_link($t);
            $roots[] = $t;
        }
    }

    return $cached = $roots;
}

/**
 * Lấy danh sách Loại dịch vụ hiển thị trên Menu & Sidebar (tương đương serviceType() bên CI4)
 */
function pccc24_get_service_types(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $terms = get_terms([
        'taxonomy'   => 'pccc_service_type',
        'hide_empty' => false,
    ]);
    if (is_wp_error($terms) || empty($terms)) {
        return $cached = [];
    }

    $enabled = array_filter($terms, function ($t) {
        $en = get_term_meta($t->term_id, '_pccc_enabled', true);
        return $en === '' || (int) $en === 1;
    });

    usort($enabled, function ($a, $b) {
        $sa = (int) get_term_meta($a->term_id, '_pccc_sorted', true);
        $sb = (int) get_term_meta($b->term_id, '_pccc_sorted', true);
        return $sa <=> $sb;
    });

    foreach ($enabled as $t) {
        $t->url = get_term_link($t);
    }

    return $cached = array_values($enabled);
}

/**
 * Lấy danh sách các cột Menu Footer và các trang con (tương đương getInitFooter() bên CI4)
 */
function pccc24_get_footer_menus(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $terms = get_terms([
        'taxonomy'   => 'pccc_page_type',
        'hide_empty' => false,
    ]);
    if (is_wp_error($terms) || empty($terms)) {
        return $cached = [];
    }

    $footer_terms = array_filter($terms, function ($t) {
        return (int) get_term_meta($t->term_id, '_pccc_position', true) === 2;
    });

    usort($footer_terms, function ($a, $b) {
        $sa = (int) get_term_meta($a->term_id, '_pccc_sorted', true);
        $sb = (int) get_term_meta($b->term_id, '_pccc_sorted', true);
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        $oa = (int) get_term_meta($a->term_id, '_pccc_old_id', true);
        $ob = (int) get_term_meta($b->term_id, '_pccc_old_id', true);
        return $oa <=> $ob;
    });

    $results = [];
    foreach ($footer_terms as $menu) {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'ID',
            'order'       => 'ASC',
            'tax_query'   => [[
                'taxonomy' => 'pccc_page_type',
                'field'    => 'term_id',
                'terms'    => $menu->term_id,
            ]],
        ]);
        $menu_pages = [];
        foreach ($pages as $p) {
            if (str_starts_with($p->post_name, 'tac-gia-')) {
                continue;
            }
            $custom_url = trim((string) get_post_meta($p->ID, '_pccc_url', true));
            $menu_pages[] = (object) [
                'ID'   => $p->ID,
                'name' => $p->post_title,
                'slug' => $p->post_name,
                'url'  => $custom_url !== '' ? $custom_url : get_permalink($p),
            ];
        }
        $menu->pages = $menu_pages;
        $results[] = $menu;
    }

    return $cached = $results;
}

/**
 * Lấy danh sách Hỗ trợ viên Zalo nổi (tương đương getZaloSupports() bên CI4)
 */
function pccc24_get_zalo_supports(): array
{
    $posts = get_posts([
        'post_type'   => 'pccc_support',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'meta_query'  => [[
            'key'   => '_pccc_support_type',
            'value' => 'zalo',
        ]],
    ]);
    $list = [];
    foreach ($posts as $p) {
        $list[] = (object) [
            'id'           => $p->ID,
            'name'         => $p->post_title,
            'content'      => (string) get_post_meta($p->ID, '_pccc_content', true),
            'support_type' => 'zalo',
        ];
    }
    return $list;
}

/**
 * Lấy toàn bộ danh sách Hỗ trợ viên cho widget góc phải dưới (#SupportApp)
 */
function pccc24_get_all_supports(): array
{
    $posts = get_posts([
        'post_type'   => 'pccc_support',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => ['menu_order' => 'ASC', 'ID' => 'ASC'],
    ]);
    $list = [];
    foreach ($posts as $p) {
        $list[] = [
            'id'           => $p->ID,
            'name'         => $p->post_title,
            'content'      => (string) get_post_meta($p->ID, '_pccc_content', true),
            'support_type' => (string) get_post_meta($p->ID, '_pccc_support_type', true),
            'image_url'    => get_the_post_thumbnail_url($p, 'full') ?: null,
            'sorted'       => (int) $p->menu_order,
        ];
    }
    return $list;
}

/**
 * Lấy danh sách 5 Sản phẩm nổi bật cho cột trái (categories/left.php)
 */
function pccc24_get_popular_products(int $limit = 5): array
{
    return get_posts([
        'post_type'   => 'pccc_product',
        'post_status' => 'publish',
        'numberposts' => $limit,
        'meta_key'    => '_pccc_popular',
        'meta_value'  => 1,
        'orderby'     => ['menu_order' => 'ASC', 'ID' => 'DESC'],
    ]);
}

/**
 * Lấy danh sách 5 Tin tức nổi bật cho cột trái (articles/left.php)
 */
function pccc24_get_popular_articles(int $limit = 5): array
{
    return get_posts([
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => $limit,
        'meta_key'    => '_pccc_popular',
        'meta_value'  => 1,
        'orderby'     => 'ID',
        'order'       => 'DESC',
    ]);
}

/**
 * Đảm bảo taxonomy pccc_page_type truy vấn đúng post_type = page và loại bỏ trang tác giả
 */
add_action('pre_get_posts', function ($wq) {
    if (!is_admin() && $wq->is_main_query() && $wq->is_tax('pccc_page_type')) {
        $wq->set('post_type', 'page');
        $author_pages = get_posts([
            'post_type'   => 'page',
            'name'        => 'tac-gia-nguyen-phu-toan',
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if (!empty($author_pages)) {
            $wq->set('post__not_in', $author_pages);
        }
    }
}, 20);

/**
 * Chuẩn hóa đường dẫn ảnh /uploads/... trong nội dung HTML cũ sang /wp-content/uploads/pccc24/...
 */
add_filter('the_content', function (string $content): string {
    return preg_replace('~(["\'\(])/?uploads/~i', '$1/wp-content/uploads/pccc24/', $content);
}, 5);

/**
 * Redirect 301 khi truy cập URL chi tiết có slug sai (ví dụ: /dich-vu/wrong-slug/8 -> /dich-vu/dung-slug/8)
 */
add_action('template_redirect', function () {
    if (is_singular(['pccc_product', 'pccc_service', 'post'])) {
        $post = get_queried_object();
        if ($post && isset($_SERVER['REQUEST_URI'])) {
            $req_path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            $expected_path = rawurldecode((string) parse_url(get_permalink($post), PHP_URL_PATH));
            if ($req_path && $expected_path && rtrim($req_path, '/') !== rtrim($expected_path, '/')) {
                wp_safe_redirect(get_permalink($post), 301);
                exit;
            }
        }
    }
});

/**
 * Tương thích ngược các endpoint /v1/... của bản CI4 sang REST API /wp-json/pccc24/v1/...
 */
add_action('init', function () {
    if (empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path && preg_match('~^/v1/(categories|supports|products/\d+)/?$~', $path, $m)) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $req = new WP_REST_Request($method, '/pccc24/v1/' . $m[1]);
        if ($method === 'POST') {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                foreach ($json as $k => $v) {
                    $req->set_param($k, $v);
                }
            }
            foreach ($_POST as $k => $v) {
                $req->set_param($k, $v);
            }
        }
        $res = rest_do_request($req);
        status_header($res->get_status());
        wp_send_json($res->get_data(), $res->get_status());
    }
}, 20);

/**
 * Hiển thị thanh phân trang chuẩn giao diện PCCC24 (.paginate-container > ul.pagination)
 */
function pccc24_render_pagination($wp_query = null): void
{
    if (!$wp_query) {
        global $wp_query;
    }
    $total_pages = (int) ($wp_query->max_num_pages ?? 1);
    if ($total_pages <= 1) {
        return;
    }
    $current = max(1, (int) ($_GET['page'] ?? get_query_var('paged') ?: 1));

    $build_url = function (int $page_num): string {
        $req_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $base_path = strtok($req_uri, '?');
        // Nếu URL đang có dạng /tin-tuc/page/2 thì chuẩn hoá về đường dẫn gốc
        $base_path = preg_replace('~/page/\d+/?$~', '', $base_path);
        $params = $_GET;
        $params['page'] = $page_num;
        return esc_url($base_path . '?' . http_build_query($params));
    };

    echo '<ul class="pagination">';
    if ($current > 1) {
        echo '<li><a href="' . $build_url(1) . '" aria-label="First">&laquo;</a></li>';
        echo '<li><a href="' . $build_url($current - 1) . '" aria-label="Previous">&lsaquo;</a></li>';
    }
    $start = max(1, $current - 2);
    $end = min($total_pages, $current + 2);
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current ? ' class="active"' : '';
        echo '<li' . $active . '><a href="' . $build_url($i) . '">' . $i . '</a></li>';
    }
    if ($current < $total_pages) {
        echo '<li><a href="' . $build_url($current + 1) . '" aria-label="Next">&rsaquo;</a></li>';
        echo '<li><a href="' . $build_url($total_pages) . '" aria-label="Last">&raquo;</a></li>';
    }
    echo '</ul>';
}

/**
 * Tự động điền sẵn Tài khoản, Email và Mật khẩu mặc định tại trang /wp-login.php (chỉ bật trên môi trường local/dev)
 * Đồng thời tắt enable_login_autofocus (setTimeout 200ms của WP) để tránh cướp focus khi nhập tự động.
 */
add_filter('enable_login_autofocus', '__return_false');
add_action('login_footer', function () {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host_only = explode(':', $host)[0];
    if (!in_array($host_only, ['localhost', '127.0.0.1', '::1'], true) && !str_ends_with($host_only, '.local') && !str_ends_with($host_only, '.test')) {
        return;
    }
    ?>
    <script>
    (function () {
        var user = document.getElementById('user_login');
        var pass = document.getElementById('user_pass');
        var rem  = document.getElementById('rememberme');
        if (user && !user.value) user.value = 'admin';
        if (pass && !pass.value) pass.value = 'pccc24@admin2026';
        if (rem) rem.checked = true;
    })();
    </script>
    <?php
});

// Theme không dùng emoji của WP (bản tĩnh GitHub Pages còn báo 404 file này).
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// wp-admin + trang đăng nhập dùng phong cách Dashboard V18 (V18.mp4) + rossark-motion-skill.
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('pccc24-admin', pccc24_theme_asset('css/admin.css'), [], '4');
    wp_enqueue_script('pccc24-admin-motion', pccc24_theme_asset('js/admin-motion.js'), [], '4', true);
});
add_action('login_enqueue_scripts', function () {
    wp_enqueue_style('pccc24-admin', pccc24_theme_asset('css/admin.css'), [], '4');
    wp_add_inline_style('pccc24-admin', '.login h1 a{background-image:url(' . esc_url(pccc24_get_setting('logo', false)) . ')}');
});
add_filter('login_headerurl', fn() => home_url('/'));
add_filter('login_headertext', fn() => 'PCCC24 - Hoàng Quân Phát');

// Khối Bento Grid Dashboard V18 trên màn hình Bảng tin (/wp-admin/index.php)
add_action('admin_notices', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'dashboard') {
        return;
    }
    $prod_count = (int) (wp_count_posts('pccc_product')->publish ?? 0);
    $post_count = (int) (wp_count_posts('post')->publish ?? 0);
    $serv_count = (int) (wp_count_posts('pccc_service')->publish ?? 0);
    $quote_obj  = wp_count_posts('pccc_quote');
    $quote_cnt  = (int) (($quote_obj->private ?? 0) + ($quote_obj->publish ?? 0));
    ?>
    <div class="v18-bento-wrap">
        <div class="v18-hero-card" data-v18-motion>
            <div>
                <span class="v18-hero-badge">● PCCC24 · DASHBOARD V18</span>
                <h2 class="v18-hero-title">Hệ thống Quản trị PCCC Hoàng Quân Phát</h2>
                <p class="v18-hero-sub">Quản lý toàn bộ danh mục thiết bị PCCC, dịch vụ thi công – bảo trì, bài viết kỹ thuật và yêu cầu báo giá của khách hàng.</p>
            </div>
            <div class="v18-rig-box" aria-hidden="true">
                <svg class="v18-motion-scene" viewBox="0 0 280 56" fill="none">
                    <!-- Lớp 1: Đồng hồ áp suất PCCC & Van kẹp chì -->
                    <circle class="v18-glow" cx="30" cy="28" r="22" fill="#0b7a4b" fill-opacity="0.16" />
                    <circle cx="30" cy="28" r="19" fill="#ffffff" stroke="#14161b" stroke-width="2" />
                    <path d="M16 35 A 15 15 0 0 1 23 15" stroke="#c8201a" stroke-width="3" stroke-linecap="round" />
                    <path d="M23 15 A 15 15 0 0 1 39 16" stroke="#0b7a4b" stroke-width="3.2" stroke-linecap="round" />
                    <path d="M39 16 A 15 15 0 0 1 44 35" stroke="#f3c623" stroke-width="3" stroke-linecap="round" />
                    <g class="v18-needle">
                        <line x1="30" y1="31" x2="30" y2="15" stroke="#c8201a" stroke-width="2.2" stroke-linecap="round" />
                        <circle cx="30" cy="28" r="3" fill="#14161b" />
                    </g>
                    <!-- Cụm van bóp & chốt an toàn (phase-delayed overlap) -->
                    <rect x="74" y="22" width="16" height="15" rx="3" fill="#14161b" />
                    <rect x="78" y="37" width="8" height="10" rx="2" fill="#c8201a" />
                    <g class="v18-lever">
                        <path d="M78 22 L106 14 L108 18 L82 25 Z" fill="#14161b" />
                    </g>
                    <g class="v18-pin">
                        <circle cx="71" cy="24" r="4" stroke="#f3c623" stroke-width="2" fill="#ffffff" />
                        <line x1="75" y1="24" x2="82" y2="24" stroke="#f3c623" stroke-width="1.8" stroke-linecap="round" />
                    </g>
                    <path d="M90 28 Q 100 28 106 25" stroke="#14161b" stroke-width="2.5" stroke-linecap="round" />
                    <circle class="v18-wisp" cx="110" cy="24" r="2.5" fill="#0b7a4b" fill-opacity="0.45" />
                    <circle class="v18-wisp" cx="115" cy="21" r="3.2" fill="#0b7a4b" fill-opacity="0.32" />
                    <circle class="v18-wisp" cx="113" cy="27" r="2.1" fill="#0b7a4b" fill-opacity="0.38" />
                    <!-- Lớp 2: Telemetry Chart Recipe (5 cột nhịp hệ thống + đèn LED trạng thái) -->
                    <rect x="130" y="8" width="142" height="40" rx="10" fill="#ffffff" fill-opacity="0.72" stroke="#14161b" stroke-opacity="0.10" />
                    <circle class="v18-led" cx="142" cy="18" r="4" fill="#0b7a4b" />
                    <text x="151" y="21" fill="#14161b" font-size="8.5" font-weight="700" font-family="system-ui, sans-serif">TELEMETRY · 24/7</text>
                    <text x="140" y="39" fill="#0b7a4b" font-size="8" font-weight="600" font-family="system-ui, sans-serif">385 SP · 100% ĐẠT</text>
                    <rect class="v18-bar" x="218" y="24" width="6" height="18" rx="3" fill="#14161b" />
                    <rect class="v18-bar" x="228" y="19" width="6" height="23" rx="3" fill="#14161b" />
                    <rect class="v18-bar" x="238" y="15" width="6" height="27" rx="3" fill="#0b7a4b" />
                    <rect class="v18-bar" x="248" y="21" width="6" height="21" rx="3" fill="#14161b" />
                    <rect class="v18-bar" x="258" y="13" width="6" height="29" rx="3" fill="#c8201a" />
                </svg>
            </div>
            <div class="v18-hero-actions">
                <a class="v18-btn-dark" href="<?= esc_url(admin_url('post-new.php?post_type=pccc_product')) ?>">+ Thêm sản phẩm mới</a>
                <a class="v18-btn-glass" href="<?= esc_url(admin_url('options-general.php?page=pccc24')) ?>">Cài đặt PCCC24</a>
                <a class="v18-btn-glass" href="<?= esc_url(home_url('/')) ?>" target="_blank" rel="noopener">Xem trang chủ ↗</a>
            </div>
        </div>

        <a class="v18-stat-card" href="<?= esc_url(admin_url('edit.php?post_type=pccc_product')) ?>">
            <div class="v18-stat-top">
                <span class="v18-stat-label">Thiết bị PCCC</span>
                <span class="v18-stat-icon">🧯</span>
            </div>
            <div class="v18-stat-value"><?= $prod_count ?></div>
            <div class="v18-stat-trend">↗ Đang hiển thị trên website</div>
        </a>

        <a class="v18-stat-card" href="<?= esc_url(admin_url('edit.php?post_type=pccc_quote')) ?>">
            <div class="v18-stat-top">
                <span class="v18-stat-label">Yêu cầu báo giá</span>
                <span class="v18-stat-icon">📞</span>
            </div>
            <div class="v18-stat-value"><?= $quote_cnt ?></div>
            <div class="v18-stat-trend">↗ Khách để lại SĐT gọi lại</div>
        </a>

        <a class="v18-stat-card" href="<?= esc_url(admin_url('edit.php')) ?>">
            <div class="v18-stat-top">
                <span class="v18-stat-label">Tin tức &amp; Kiến thức</span>
                <span class="v18-stat-icon">📰</span>
            </div>
            <div class="v18-stat-value"><?= $post_count ?></div>
            <div class="v18-stat-trend">↗ Bài viết chuẩn SEO</div>
        </a>

        <a class="v18-stat-card" href="<?= esc_url(admin_url('edit.php?post_type=pccc_service')) ?>">
            <div class="v18-stat-top">
                <span class="v18-stat-label">Dịch vụ &amp; Dự án</span>
                <span class="v18-stat-icon">🛠️</span>
            </div>
            <div class="v18-stat-value"><?= $serv_count ?></div>
            <div class="v18-stat-trend">↗ Thi công · Bảo trì · Dự án</div>
        </a>
    </div>
    <?php
});

/**
 * Bản WebP thu nhỏ cho ảnh nằm trong nội dung bài (không phải ảnh đại diện nên không có -card/-hero).
 * Tạo một lần khi cần rồi dùng lại; lỗi thì trả ảnh gốc.
 */
function pccc24_upload_webp(string $url, int $max_w): string
{
    $path = ABSPATH . ltrim(rawurldecode((string) wp_parse_url($url, PHP_URL_PATH)), '/');
    $dst  = preg_replace('/\.[^.\/]+$/', "-w$max_w.webp", $path);
    if (!is_file($dst) && is_file($path)) {
        $ed = wp_get_image_editor($path);
        // resize() báo lỗi khi ảnh đã nhỏ hơn max_w: khi đó chỉ đổi sang WebP.
        if (is_wp_error($ed) || ($ed->get_size()['width'] > $max_w && is_wp_error($ed->resize($max_w, null))) || is_wp_error($ed->save($dst, 'image/webp'))) {
            return $url;
        }
    }
    return is_file($dst) ? preg_replace('/\.[^.\/]+$/', "-w$max_w.webp", $url) : $url;
}

/**
 * Nội dung "Giới thiệu" (trang /pages/gioi-thieu) tách thành từng phần cho trang Liên hệ,
 * để sửa ở wp-admin một chỗ là cả hai trang cùng đổi.
 */
function pccc24_about_data(): array
{
    $page = get_page_by_path('gioi-thieu');
    $html = $page ? $page->post_content : '';
    $img_after = fn(string $heading) => preg_match('~' . preg_quote($heading, '~') . '.*?<img[^>]+src="([^"]+)"~su', $html, $m) ? $m[1] : '';

    preg_match_all('~<p[^>]*>(.*?)</p>~su', $html, $ps);
    $clean = fn($t) => preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', html_entity_decode(wp_strip_all_tags($t)));
    $paras = array_values(array_filter(array_map($clean, $ps[1]), fn($t) => mb_strlen($t) > 120));

    // Khách hàng tiêu biểu: một hàng ảnh rồi một hàng link tên dự án (2 bảng riêng) -> ghép theo thứ tự.
    $tail = (string) strstr($html, 'KHÁCH HÀNG TIÊU BIỂU');
    preg_match_all('~<img[^>]+src="([^"]+)"~', $tail, $imgs);
    preg_match_all('~<a[^>]+href="(/dich-vu/[^"]+)"[^>]*>(.*?)</a>~su', $tail, $links, PREG_SET_ORDER);
    $projects = [];
    foreach ($links as $i => [, $url, $title]) {
        $projects[] = ['url' => $url, 'title' => $clean($title), 'img' => $imgs[1][$i] ?? ''];
    }

    return [
        'paras'       => array_slice($paras, 0, 4),
        'hsnl_url'    => preg_match('~href="(https://drive\.google\.com/[^"]+)"~', $html, $m) ? $m[1] : '',
        'hsnl_img'    => preg_match('~<img[^>]+src="([^"]+)"~', $html, $m) ? $m[1] : '',
        'certificate' => $img_after('GIẤY CHỨNG NHẬN'),
        'partners'    => $img_after('ĐỐI TÁC TIÊU BIỂU'),
        'projects'    => $projects,
        'url'         => $page ? get_permalink($page) : home_url('/pages/gioi-thieu'),
    ];
}
