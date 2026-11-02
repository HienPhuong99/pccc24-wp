<?php
/**
 * Plugin Name: PCCC24 Core
 * Description: Data model (CPT/taxonomy), URL cũ của pccc24.com, REST API v1 và cài đặt chung.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

const PCCC24_SETTING_KEYS = [
    'phone' => 'Điện thoại', 'company' => 'Tên công ty', 'address' => 'Địa chỉ', 'email' => 'Email',
    'gpkd' => 'GPKD', 'logo' => 'Logo', 'phone_technique' => 'ĐT kỹ thuật', 'phone_sale' => 'ĐT bán hàng',
    'site_title' => 'Tiêu đề website', 'site_description' => 'Mô tả website', 'facebook' => 'Facebook',
    'twitter' => 'Twitter', 'youtube' => 'Youtube', 'zalo' => 'Zalo', 'logo_seo' => 'Logo SEO',
];

/* ---------- Data model ---------- */

add_action('init', function () {
    $cpt = function ($type, $name, $icon, $extra = []) {
        register_post_type($type, $extra + [
            'label' => $name, 'public' => true, 'show_in_rest' => true, 'menu_icon' => $icon,
            'labels' => ['name' => $name, 'singular_name' => $name, 'add_new' => 'Thêm mới', 'add_new_item' => "Thêm $name",
                'edit_item' => "Sửa $name", 'view_item' => "Xem $name", 'search_items' => "Tìm $name", 'not_found' => 'Chưa có mục nào', 'all_items' => "Tất cả $name"],
            'has_archive' => false, 'rewrite' => false, 'query_var' => false,
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes'],
        ]);
    };
    $cpt('pccc_product', 'Sản phẩm', 'dashicons-cart');
    $cpt('pccc_service', 'Dịch vụ', 'dashicons-hammer');
    $cpt('pccc_slider', 'Slider', 'dashicons-images-alt2', ['public' => false, 'show_ui' => true]);
    $cpt('pccc_policy', 'Chính sách', 'dashicons-shield', ['public' => false, 'show_ui' => true]);
    $cpt('pccc_support', 'Hỗ trợ viên', 'dashicons-phone', ['public' => false, 'show_ui' => true]);
    $cpt('pccc_quote', 'Yêu cầu báo giá', 'dashicons-megaphone', ['public' => false, 'show_ui' => true, 'show_in_rest' => false, 'supports' => ['title', 'custom-fields']]);

    $tax = fn($tax, $types, $name, $hier) => register_taxonomy($tax, $types, [
        'label' => $name, 'public' => true, 'hierarchical' => $hier, 'show_in_rest' => true,
        'show_admin_column' => true, 'rewrite' => false, 'query_var' => false,
    ]);
    $tax('pccc_product_cat', 'pccc_product', 'Danh mục sản phẩm', true);
    $tax('pccc_service_type', 'pccc_service', 'Loại dịch vụ', false);
    $tax('pccc_page_type', 'page', 'Nhóm trang', false);

    pccc24_add_rewrites();
    if (get_option('pccc24_flush_rewrites')) {
        delete_option('pccc24_flush_rewrites');
        flush_rewrite_rules(false); // WP tự hoãn tới wp_loaded, lúc đó rule đã có slug nhóm trang hiện tại
    }
});

/* ---------- URL cũ (app/Config/Routes.php của bản CI4) ---------- */

function pccc24_add_rewrites()
{
    $r = fn($re, $q) => add_rewrite_rule($re, 'index.php?' . $q, 'top');
    $r('san-pham/?$', 'pccc_view=products');
    $r('san-pham/tim-kiem/?$', 'pccc_view=search');
    $r('san-pham/([^/]+)/([0-9]+)/?$', 'pccc_type=pccc_product&pccc_old_id=$matches[2]');
    $r('san-pham/([^/]+)/?$', 'pccc_product_cat=$matches[1]');
    $r('dich-vu/?$', 'pccc_view=services');
    $r('dich-vu/([^/]+)/([0-9]+)/?$', 'pccc_type=pccc_service&pccc_old_id=$matches[2]');
    $r('dich-vu/([^/]+)/?$', 'pccc_service_type=$matches[1]');
    $r('tin-tuc/?$', 'pccc_view=articles');
    $r('tin-tuc/page/([0-9]+)/?$', 'pccc_view=articles&paged=$matches[1]');
    $r('tin-tuc/([^/]+)/([0-9]+)/?$', 'pccc_type=post&pccc_old_id=$matches[2]');
    $r('pages/type/([^/]+)/?$', 'pccc_page_type=$matches[1]');
    $r('pages/([^/]+)/?$', 'pagename=$matches[1]');
    $r('contacts/?$', 'pccc_view=contacts');
    // /{page-type-slug}: chỉ các slug nhóm trang đang có, tránh nuốt route khác của WP.
    $slugs = get_terms(['taxonomy' => 'pccc_page_type', 'fields' => 'slugs', 'hide_empty' => false]);
    if ($slugs && !is_wp_error($slugs)) {
        $r('(' . implode('|', array_map('preg_quote', $slugs)) . ')/?$', 'pccc_page_type=$matches[1]');
    }
}

add_filter('query_vars', fn($v) => array_merge($v, [
    'pccc_view', 'pccc_type', 'pccc_old_id', 'pccc_product_cat', 'pccc_service_type', 'pccc_page_type',
]));

function pccc24_find_by_old_id($type, $old_id)
{
    $ids = get_posts([
        'post_type' => $type, 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids',
        'meta_key' => '_pccc_old_id', 'meta_value' => (int) $old_id,
    ]);
    return $ids[0] ?? 0;
}

// Dịch query var tuỳ biến sang truy vấn WP chuẩn để theme dùng is_singular()/is_tax()/have_posts().
add_filter('request', function ($q) {
    if (!empty($q['pccc_old_id'])) {
        // pccc_type là query var công khai (?pccc_type=...): chỉ nhận 3 loại có URL cũ.
        $type = in_array($q['pccc_type'] ?? '', ['pccc_product', 'pccc_service', 'post'], true) ? $q['pccc_type'] : 'pccc_product';
        $id = pccc24_find_by_old_id($type, $q['pccc_old_id']);
        return $id ? ['p' => $id, 'post_type' => $type] : ['error' => '404'];
    }
    foreach (['pccc_product_cat', 'pccc_service_type', 'pccc_page_type'] as $tax) {
        if (!empty($q[$tax])) {
            return term_exists($q[$tax], $tax) ? ['taxonomy' => $tax, 'term' => $q[$tax]] + $q : ['error' => '404'];
        }
    }
    $views = ['products' => 'pccc_product', 'search' => 'pccc_product', 'services' => 'pccc_service', 'articles' => 'post'];
    if (isset($q['pccc_view'], $views[$q['pccc_view']])) {
        $q['post_type'] = $views[$q['pccc_view']];
        if ($q['pccc_view'] === 'search') {
            $q['s'] = sanitize_text_field(wp_unslash($_GET['q'] ?? $_GET['s'] ?? ''));
        }
    }
    return $q;
});

// /contacts không có post nào: tránh 404, theme render theo get_query_var('pccc_view').
add_action('pre_get_posts', function ($wq) {
    if ($wq->is_main_query() && $wq->get('pccc_view') === 'contacts') {
        $wq->set('post_type', 'page');
        $wq->set('posts_per_page', 1);
        $wq->set('pagename', 'lien-he');
    }
    if (!$wq->is_main_query() || is_admin() || !($wq->is_tax() || $wq->get('pccc_view'))) {
        return;
    }
    // Giống controller CI4: paginate(20), ?page=N, ?sorted=1..4 (tên/giá), mặc định sorted ASC + mới nhất.
    $wq->set('posts_per_page', 20);
    if (!empty($_GET['page'])) {
        $wq->set('paged', max(1, (int) $_GET['page']));
    }
    $type = $wq->get('post_type') ?: ($wq->is_tax('pccc_service_type') ? 'pccc_service' : 'pccc_product');
    if ($type === 'post') {
        $wq->set('orderby', 'date');
        return;
    }
    $sorted = (int) ($_GET['sorted'] ?? 0);
    if ($sorted === 1 || $sorted === 2) {
        $wq->set('orderby', ['title' => $sorted === 1 ? 'ASC' : 'DESC']);
    } elseif ($sorted === 3 || $sorted === 4) {
        $wq->set('meta_key', '_pccc_price');
        $wq->set('orderby', ['meta_value_num' => $sorted === 3 ? 'ASC' : 'DESC']);
    } else {
        $wq->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
        if ($type === 'pccc_service') {
            $wq->set('pccc_zero_last', 1);
        }
    }
});
// Dịch vụ: sorted = 0 xuống cuối (ServiceController gốc).
add_filter('posts_orderby', function ($orderby, $wq) {
    global $wpdb;
    return $wq->get('pccc_zero_last') ? "{$wpdb->posts}.menu_order = 0 ASC, $orderby" : $orderby;
}, 10, 2);

// URL gốc không có "/" cuối; đừng để WP redirect các view tuỳ biến.
add_filter('redirect_canonical', fn($url) => get_query_var('pccc_view') ? false : $url);

// Template: theme có thể cung cấp file theo tên view (vd. contacts.php, products.php).
add_filter('template_include', function ($tpl) {
    $view = get_query_var('pccc_view');
    return ($view && ($t = locate_template("$view.php"))) ? $t : $tpl;
});

function pccc24_old_id($post)
{
    return get_post_meta($post->ID, '_pccc_old_id', true) ?: $post->ID;
}

add_filter('post_type_link', function ($url, $post) {
    $base = ['pccc_product' => 'san-pham', 'pccc_service' => 'dich-vu'][$post->post_type] ?? null;
    return $base ? home_url("/$base/{$post->post_name}/" . pccc24_old_id($post)) : $url;
}, 10, 2);
add_filter('post_link', fn($url, $post) => $post->post_type === 'post'
    ? home_url("/tin-tuc/{$post->post_name}/" . pccc24_old_id($post)) : $url, 10, 2);
add_filter('page_link', fn($url, $id) => ($p = get_post($id)) && $p->post_name && (int) get_option('page_on_front') !== $id
    ? home_url("/pages/{$p->post_name}") : $url, 10, 2);
add_filter('term_link', function ($url, $term) {
    $base = ['pccc_product_cat' => 'san-pham', 'pccc_service_type' => 'dich-vu', 'pccc_page_type' => 'pages/type'][$term->taxonomy] ?? null;
    return $base ? home_url("/$base/{$term->slug}") : $url;
}, 10, 2);

// Sản phẩm/dịch vụ/bài mới tạo trong wp-admin: old_id = post ID (ID WP luôn lớn hơn ID cũ sau migrate).
add_action('save_post', function ($id, $post) {
    if (in_array($post->post_type, ['pccc_product', 'pccc_service', 'post'], true) && !get_post_meta($id, '_pccc_old_id', true)) {
        update_post_meta($id, '_pccc_old_id', $id);
    }
}, 10, 2);

register_activation_hook(__FILE__, function () {
    do_action('init');
    flush_rewrite_rules();
});
// Thêm/sửa/xoá nhóm trang: flush ngay lúc đó vẫn dựng rule từ danh sách slug đọc lúc init (chưa có slug mới
// -> /{slug-mới} bị 404). Đánh dấu để request sau flush, khi pccc24_add_rewrites() đã đọc slug hiện tại.
foreach (['created', 'edited', 'delete'] as $ev) {
    add_action("{$ev}_pccc_page_type", fn() => update_option('pccc24_flush_rewrites', 1));
}

/* ---------- REST API (tương đương /v1 của bản CI4) ---------- */

function pccc24_product_json($post)
{
    $m = fn($k) => get_post_meta($post->ID, "_pccc_$k", true);
    $cat = wp_get_post_terms($post->ID, 'pccc_product_cat');
    return [
        'id' => (int) pccc24_old_id($post), 'wp_id' => $post->ID, 'name' => $post->post_title,
        'title' => $m('title'), 'slug' => $post->post_name, 'url' => get_permalink($post),
        'category_id' => $cat ? (int) get_term_meta($cat[0]->term_id, '_pccc_old_id', true) : null,
        'price' => (int) $m('price'), 'image_url' => (function_exists('pccc24_post_image_url') ? pccc24_post_image_url($post, 'card') : get_the_post_thumbnail_url($post, 'full')) ?: null,
        'description' => $m('description'), 'content' => apply_filters('the_content', $post->post_content),
        'small_content' => $post->post_excerpt, 'popular' => (int) $m('popular'), 'youtube_video' => $m('youtube_video'),
        'point' => (float) $m('point'), 'catalog_url' => $m('catalog_url'), 'specs' => $m('specs') ?: [],
    ];
}

add_action('rest_api_init', function () {
    register_rest_route('pccc24/v1', '/categories', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => fn() => ['categories' => array_map(fn($t) => [
            'id' => (int) get_term_meta($t->term_id, '_pccc_old_id', true), 'name' => $t->name, 'slug' => $t->slug,
            'parent_id' => $t->parent ? (int) get_term_meta($t->parent, '_pccc_old_id', true) : null,
            'url' => get_term_link($t), 'count' => $t->count,
        ], get_terms(['taxonomy' => 'pccc_product_cat', 'hide_empty' => false]))],
    ]);
    register_rest_route('pccc24/v1', '/products/(?P<id>\d+)', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => function ($req) {
            $id = pccc24_find_by_old_id('pccc_product', $req['id']);
            return $id ? pccc24_product_json(get_post($id)) : new WP_Error('not_found', 'Không tìm thấy sản phẩm', ['status' => 404]);
        },
    ]);
    register_rest_route('pccc24/v1', '/supports', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => fn() => ['supports' => array_map(fn($p) => [
            'id' => $p->ID, 'name' => $p->post_title, 'content' => get_post_meta($p->ID, '_pccc_content', true),
            'support_type' => get_post_meta($p->ID, '_pccc_support_type', true),
            'image_url' => get_the_post_thumbnail_url($p, 'full') ?: null, 'sorted' => $p->menu_order,
        ], get_posts(['post_type' => 'pccc_support', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']))],
    ]);
    register_rest_route('pccc24/v1', '/quotes', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $req) {
            if (!empty($req->get_param('website'))) {
                return new WP_Error('spam_detected', 'Yêu cầu không hợp lệ.', ['status' => 400]);
            }
            $name  = mb_substr(sanitize_text_field((string) $req->get_param('name')), 0, 100);
            $phone = preg_replace('/[^0-9+]/', '', (string) $req->get_param('phone'));
            $need  = sanitize_text_field((string) ($req->get_param('need') ?: 'Bình chữa cháy'));
            $note  = mb_substr(sanitize_textarea_field((string) $req->get_param('note')), 0, 1000);
            $url   = esc_url_raw((string) $req->get_param('source_url'));
            if ($name === '' || !preg_match('/^(0|\+84)\d{8,10}$/', $phone)) {
                return new WP_Error('invalid_input', 'Vui lòng nhập họ tên và số điện thoại hợp lệ.', ['status' => 422]);
            }
            $ip  = (string) ($_SERVER['REMOTE_ADDR'] ?? ''); // không tin header client gửi lên: giả được để né giới hạn
            $key = 'pccc24_qrl_' . md5($ip);
            $cnt = (int) get_transient($key);
            if ($cnt >= 5) {
                return new WP_Error('rate_limited', 'Bạn đã gửi quá 5 yêu cầu trong giờ qua. Vui lòng gọi trực tiếp.', ['status' => 429]);
            }
            set_transient($key, $cnt + 1, HOUR_IN_SECONDS);
            $post_id = wp_insert_post([
                'post_type'   => 'pccc_quote',
                'post_status' => 'private',
                'post_title'  => "$name — $phone ($need)",
                'meta_input'  => [
                    '_pccc_phone'      => $phone,
                    '_pccc_need'       => $need,
                    '_pccc_note'       => $note,
                    '_pccc_source_url' => $url,
                ],
            ]);
            if (is_wp_error($post_id) || !$post_id) {
                return new WP_Error('save_failed', 'Không lưu được yêu cầu.', ['status' => 500]);
            }
            $to = pccc24_setting('email');
            if ($to) {
                @wp_mail($to, "[PCCC24] Yêu cầu gọi lại báo giá: $name ($phone)", "Khách: $name\nSĐT: $phone\nNhu cầu: $need\nGhi chú: $note\nTrang: $url");
            }
            return new WP_REST_Response(['ok' => true, 'id' => (int) $post_id, 'message' => 'Đã nhận yêu cầu! Kỹ thuật viên sẽ gọi lại báo giá sớm nhất.'], 201);
        },
    ]);
    // pccc24.com không bán hàng trực tuyến (route POST v1/carts gốc không có controller) -> không có API đặt hàng.
});

/* ---------- wp-admin: metabox các meta _pccc_* ---------- */

const PCCC24_FIELDS = [
    'pccc_product' => [
        'price' => 'Giá (0 = Liên hệ)', 'popular' => 'Nổi bật (1/0)', 'title' => 'SEO title', 'description' => 'SEO description',
        'youtube_video' => 'YouTube', 'catalog_url' => 'Catalog URL', 'policy_ids' => 'ID chính sách (vd. 1,2,3)',
        'specs' => 'Thông số kỹ thuật (mỗi dòng: Tên | Giá trị)',
    ],
    'pccc_service' => ['title' => 'SEO title', 'description' => 'SEO description', 'canonical_url' => 'Canonical URL'],
    'post' => ['title' => 'SEO title', 'description' => 'SEO description', 'popular' => 'Nổi bật (1/0)', 'video' => 'Video', 'canonical_url' => 'Canonical URL'],
    'page' => ['url' => 'Link ngoài (để trống = trang thường)'],
    'pccc_slider' => ['url' => 'Link', 'position' => 'Vị trí'],
    'pccc_policy' => ['url' => 'Link', 'position' => 'Vị trí (1 = dải cam kết, 2 = banner)'],
    'pccc_support' => ['content' => 'SĐT / Zalo / Email', 'support_type' => 'Loại (phone, zalo, email...)'],
    'pccc_quote' => ['phone' => 'Số điện thoại', 'need' => 'Nhu cầu', 'note' => 'Ghi chú', 'source_url' => 'Trang gửi'],
];

// Nội dung migrate là HTML cũ: trình soạn thảo cổ điển hiển thị metabox ngay, không bị gói thành "Classic block".
add_filter('use_block_editor_for_post_type', fn($use, $type) => isset(PCCC24_FIELDS[$type]) ? false : $use, 10, 2);

add_action('add_meta_boxes', function () {
    foreach (array_keys(PCCC24_FIELDS) as $type) {
        add_meta_box('pccc24_fields', 'Thông tin PCCC24', 'pccc24_fields_box', $type, 'normal', 'high');
    }
});

function pccc24_fields_box($post)
{
    wp_nonce_field('pccc24_fields', 'pccc24_nonce');
    echo '<table class="form-table">';
    foreach (PCCC24_FIELDS[$post->post_type] as $k => $label) {
        $v = get_post_meta($post->ID, "_pccc_$k", true);
        if ($k === 'specs') {
            // Bài mới: meta rỗng '' -> (array) thành [''] -> phải lọc, không thì đọc $s['name'] trên chuỗi = Fatal error.
            $v = implode("\n", array_map(fn($s) => ($s['name'] ?? '') . ' | ' . ($s['content'] ?? ''), array_filter((array) $v, 'is_array')));
        } elseif ($k === 'policy_ids') {
            $v = implode(',', (array) $v);
        }
        $input = $k === 'specs'
            ? sprintf('<textarea name="pccc24[%s]" rows="8" class="large-text code">%s</textarea>', $k, esc_textarea($v))
            : sprintf('<input name="pccc24[%s]" value="%s" class="large-text">', $k, esc_attr($v));
        printf('<tr><th>%s</th><td>%s</td></tr>', esc_html($label), $input);
    }
    echo '</table>';
}

add_action('save_post', function ($id, $post) {
    if (!isset(PCCC24_FIELDS[$post->post_type], $_POST['pccc24'], $_POST['pccc24_nonce'])
        || !wp_verify_nonce($_POST['pccc24_nonce'], 'pccc24_fields')
        || wp_is_post_autosave($id) || !current_user_can('edit_post', $id)) {
        return;
    }
    $in = wp_unslash((array) $_POST['pccc24']);
    foreach (array_keys(PCCC24_FIELDS[$post->post_type]) as $k) {
        $v = (string) ($in[$k] ?? '');
        $v = match (true) {
            in_array($k, ['price', 'popular', 'position'], true) => (int) $v,
            str_ends_with($k, 'url') || in_array($k, ['youtube_video', 'video'], true) => esc_url_raw($v),
            $k === 'policy_ids' => array_values(array_filter(array_map('intval', explode(',', $v)))),
            $k === 'specs' => array_values(array_filter(array_map(function ($line) {
                [$name, $content] = array_map('trim', explode('|', $line, 2)) + [1 => ''];
                return $name === '' ? null : ['name' => sanitize_text_field($name), 'content' => sanitize_text_field($content)];
            }, explode("\n", $v)))),
            default => sanitize_text_field($v),
        };
        update_post_meta($id, "_pccc_$k", $v);
    }
}, 10, 2);

add_filter('manage_pccc_product_posts_columns', fn($c) => array_slice($c, 0, 2) + ['old_id' => 'ID cũ', 'price' => 'Giá'] + $c);
add_action('manage_pccc_product_posts_custom_column', function ($col, $id) {
    $v = (int) get_post_meta($id, "_pccc_$col", true);
    echo esc_html($col === 'price' ? ($v ? number_format($v, 0, ',', '.') . 'đ' : 'Liên hệ') : (string) $v);
}, 10, 2);

/* ---------- Cài đặt chung (bảng settings cũ) ---------- */

function pccc24_setting($key)
{
    return get_option('pccc24_settings', [])[$key] ?? '';
}

add_action('admin_menu', function () {
    add_options_page('PCCC24', 'PCCC24', 'manage_options', 'pccc24', function () {
        echo '<div class="wrap"><h1>Cài đặt PCCC24</h1><form method="post" action="options.php">';
        settings_fields('pccc24');
        $opt = get_option('pccc24_settings', []);
        echo '<table class="form-table">';
        foreach (PCCC24_SETTING_KEYS as $k => $label) {
            printf('<tr><th><label for="pccc24_%1$s">%2$s</label></th><td><textarea id="pccc24_%1$s" name="pccc24_settings[%1$s]" rows="2" class="large-text">%3$s</textarea></td></tr>',
                esc_attr($k), esc_html($label), esc_textarea($opt[$k] ?? ''));
        }
        echo '</table>';
        submit_button();
        echo '</form></div>';
    });
});
add_action('admin_init', fn() => register_setting('pccc24', 'pccc24_settings', [
    'type' => 'array',
    'sanitize_callback' => fn($v) => array_map('wp_kses_post', array_intersect_key((array) $v, PCCC24_SETTING_KEYS)),
]));
