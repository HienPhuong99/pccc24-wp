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
 * Lấy URL ảnh đại diện của post (sản phẩm, bài viết, dịch vụ, slider, chính sách)
 */
function pccc24_post_image_url($post = null): string
{
    $post = get_post($post);
    if (!$post) {
        return pccc24_no_image_url();
    }
    $url = get_the_post_thumbnail_url($post, 'full');
    if (!$url) {
        return pccc24_no_image_url();
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
    if ($path && preg_match('~^/v1/(categories|supports|products/\d+|carts|orders)/?$~', $path, $m)) {
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
