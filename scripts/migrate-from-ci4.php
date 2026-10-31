<?php
/**
 * Migrate pccc24_db (CodeIgniter 4) -> WordPress. Chạy lại được nhiều lần (upsert theo meta _pccc_src).
 *   php tools/wp-cli.phar eval-file scripts/migrate-from-ci4.php
 * Yêu cầu: đã copy D:\Code\PCCC24\public\uploads -> wp-content/uploads/pccc24 (scripts/setup.ps1 làm việc này).
 */

defined('ABSPATH') || exit;

$src = new mysqli(getenv('PCCC24_SRC_HOST') ?: '127.0.0.1', 'root', getenv('PCCC24_SRC_PASS') ?: '000000', 'pccc24_db');
$src->set_charset('utf8mb4');
$rows = fn($sql) => $src->query($sql)->fetch_all(MYSQLI_ASSOC);

wp_suspend_cache_addition(true);
wp_defer_term_counting(true);
kses_remove_filters(); // nội dung HTML gốc giữ nguyên (đã tin cậy từ DB cũ)

global $uploads, $missing;
$uploads = wp_upload_dir();
$missing = [];

/** Link tuyệt đối của site cũ -> đường dẫn tương đối trên WP. */
function pccc24_fix_html($html)
{
    if (!$html) {
        return '';
    }
    $html = preg_replace('~https?://(?:img\.)?pccc24\.com/uploads?/~i', '/wp-content/uploads/pccc24/', $html);
    $html = preg_replace('~(?:\.\./)+(san-pham|dich-vu|tin-tuc|pages)/~', '/$1/', $html);
    return preg_replace('~https?://(?:www\.)?pccc24\.com/?~i', '/', $html);
}

function pccc24_find($key, $type = 'any')
{
    $ids = get_posts([
        'post_type' => $type, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids',
        'meta_key' => '_pccc_src', 'meta_value' => $key, 'suppress_filters' => true,
    ]);
    return $ids[0] ?? 0;
}

function pccc24_upsert($key, array $post, array $meta = [])
{
    $post['ID'] = pccc24_find($key, $post['post_type']);
    $post += ['post_status' => 'publish'];
    $id = wp_insert_post(wp_slash($post), true);
    if (is_wp_error($id)) {
        WP_CLI::warning("$key: " . $id->get_error_message());
        return 0;
    }
    foreach ($meta + ['_pccc_src' => $key] as $k => $v) {
        update_post_meta($id, $k, wp_slash($v));
    }
    return $id;
}

/** Đăng ký file đã copy thành attachment (không sinh thumbnail để chạy nhanh). */
function pccc24_attachment($file)
{
    global $uploads, $missing;
    $file = trim((string) $file);
    if ($file === '') {
        return 0;
    }
    $file = preg_replace('~^(?:https?://(?:img\.)?pccc24\.com)?/?uploads/~i', '', $file);
    if (preg_match('~^https?://~', $file)) {
        return 0;
    }
    $key = "file:$file";
    if ($id = pccc24_find($key, 'attachment')) {
        return $id;
    }
    $path = $uploads['basedir'] . '/pccc24/' . $file;
    // Bản local thiếu ảnh mới -> lấy từ site thật (chỉ file ảnh, không đi ra ngoài thư mục).
    if (!is_file($path) && preg_match('~^[^/\\\\]+\.(jpe?g|png|gif|webp|svg)$~i', $file) && !getenv('PCCC24_OFFLINE')) {
        $res = wp_remote_get('https://pccc24.com/uploads/' . rawurlencode($file), ['timeout' => 30, 'stream' => true, 'filename' => $path]);
        if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
            @unlink($path);
        }
    }
    if (!is_file($path)) {
        $missing[$file] = true;
        return 0;
    }
    $id = wp_insert_attachment([
        'post_title' => pathinfo($file, PATHINFO_FILENAME),
        'post_mime_type' => wp_check_filetype($file)['type'] ?: 'image/jpeg',
        'post_status' => 'inherit',
    ], $path);
    $size = @getimagesize($path);
    wp_update_attachment_metadata($id, ['width' => $size[0] ?? 0, 'height' => $size[1] ?? 0, 'file' => "pccc24/$file", 'sizes' => []]);
    update_post_meta($id, '_pccc_src', $key);
    return $id;
}

function pccc24_term($tax, $key, $name, $slug, $args = [], $meta = [])
{
    $found = get_terms(['taxonomy' => $tax, 'hide_empty' => false, 'meta_key' => '_pccc_src', 'meta_value' => $key, 'fields' => 'ids']);
    $args += ['slug' => $slug];
    $r = $found ? wp_update_term($found[0], $tax, ['name' => $name] + $args) : wp_insert_term($name, $tax, $args);
    if (is_wp_error($r)) {
        WP_CLI::warning("$key: " . $r->get_error_message());
        return 0;
    }
    foreach ($meta + ['_pccc_src' => $key] as $k => $v) {
        update_term_meta($r['term_id'], $k, $v);
    }
    return $r['term_id'];
}

$date = fn($r) => ['post_date' => $r['created_at'] ?: current_time('mysql')];

/* Settings */
update_option('pccc24_settings', array_column($rows('SELECT setting_key, setting_value FROM settings'), 'setting_value', 'setting_key'));
update_option('blogname', 'PCCC24');
update_option('blogdescription', pccc24_setting('site_description'));
WP_CLI::log('settings: ' . count(get_option('pccc24_settings')));

/* Media library (bảng photos) */
foreach ($rows('SELECT * FROM photos') as $r) {
    if ($id = pccc24_attachment($r['image_url'])) {
        update_post_meta($id, '_pccc_photo_id', $r['id']);
    }
}

/* Danh mục sản phẩm: tạo trước, gán cha sau (cha có thể đứng sau con) */
$cats = $rows('SELECT * FROM categories ORDER BY id');
$catMap = [];
foreach ($cats as $r) {
    $catMap[$r['id']] = pccc24_term('pccc_product_cat', "category:{$r['id']}", $r['name'], $r['slug'], [
        'description' => (string) $r['description'],
    ], [
        '_pccc_old_id' => $r['id'], '_pccc_content' => pccc24_fix_html($r['content']), '_pccc_sorted' => (int) $r['sorted'],
        '_pccc_popular' => (int) $r['popular'], '_pccc_home_sorted' => (int) $r['home_sorted'],
        '_pccc_image' => pccc24_attachment($r['image_url']),
    ]);
}
foreach ($cats as $r) {
    if ($r['parent_id'] && isset($catMap[$r['parent_id']])) {
        wp_update_term($catMap[$r['id']], 'pccc_product_cat', ['parent' => $catMap[$r['parent_id']]]);
    }
}
// Bộ lọc thuộc tính (bảng properties: nhóm parent_id NULL + giá trị con) theo danh mục
$filters = [];
foreach ($rows('SELECT * FROM properties ORDER BY sorted, id') as $p) {
    if (!$p['parent_id']) {
        $filters[$p['category_id']][$p['id']] = ['id' => (int) $p['id'], 'name' => $p['name'], 'values' => []];
    }
}
foreach ($rows('SELECT * FROM properties WHERE parent_id IS NOT NULL ORDER BY sorted, id') as $p) {
    foreach ($filters as $cid => $groups) {
        if (isset($groups[$p['parent_id']])) {
            $filters[$cid][$p['parent_id']]['values'][] = ['id' => (int) $p['id'], 'name' => $p['name']];
        }
    }
}
foreach ($filters as $cid => $groups) {
    if (isset($catMap[$cid])) {
        update_term_meta($catMap[$cid], '_pccc_filters', array_values($groups));
    }
}
WP_CLI::log('categories: ' . count($catMap));

/* Sản phẩm */
$specs = [];
foreach ($rows('SELECT * FROM product_properties ORDER BY id') as $p) {
    $specs[$p['product_id']][] = ['name' => $p['name'], 'content' => $p['content']];
}
// product_policy_products.product_policy_id trỏ vào bảng policies (id 1-4), không phải product_policies.
$policyIds = [];
foreach ($rows('SELECT product_id, product_policy_id FROM product_policy_products ORDER BY id') as $p) {
    $policyIds[$p['product_id']][] = (int) $p['product_policy_id'];
}
$n = 0;
foreach ($rows('SELECT * FROM products ORDER BY id') as $r) {
    $id = pccc24_upsert("product:{$r['id']}", [
        'post_type' => 'pccc_product', 'post_title' => $r['name'], 'post_name' => $r['slug'],
        'post_content' => pccc24_fix_html($r['content']), 'post_excerpt' => pccc24_fix_html($r['small_content']),
        'menu_order' => (int) $r['sorted'],
    ] + $date($r), [
        '_pccc_old_id' => (int) $r['id'], '_pccc_title' => (string) $r['title'], '_pccc_description' => (string) $r['description'],
        '_pccc_price' => (int) $r['price'], '_pccc_popular' => (int) $r['popular'], '_pccc_youtube_video' => (string) $r['youtube_video'],
        '_pccc_comment' => (int) $r['comment'], '_pccc_point' => (float) $r['point'], '_pccc_catalog_url' => (string) $r['catalog_url'],
        '_pccc_supplier_id' => (int) $r['supplier_id'], '_pccc_specs' => $specs[$r['id']] ?? [],
        '_pccc_policy_ids' => $policyIds[$r['id']] ?? [], // = _pccc_old_id của pccc_policy
    ]);
    if (!$id) {
        continue;
    }
    wp_set_object_terms($id, isset($catMap[$r['category_id']]) ? [$catMap[$r['category_id']]] : [], 'pccc_product_cat');
    ($thumb = pccc24_attachment($r['image_url'])) ? set_post_thumbnail($id, $thumb) : delete_post_thumbnail($id);
    $n++;
}
WP_CLI::log("products: $n");

/* Tin tức -> post */
$n = 0;
foreach ($rows('SELECT * FROM articles ORDER BY id') as $r) {
    $id = pccc24_upsert("article:{$r['id']}", [
        'post_type' => 'post', 'post_title' => $r['name'] ?: $r['title'], 'post_name' => $r['slug'],
        'post_content' => pccc24_fix_html($r['content']), 'post_excerpt' => (string) $r['description'],
    ] + $date($r), [
        '_pccc_old_id' => (int) $r['id'], '_pccc_title' => (string) $r['title'], '_pccc_description' => (string) $r['description'],
        '_pccc_article_type_id' => (int) $r['article_type_id'], '_pccc_popular' => (int) $r['popular'], '_pccc_view' => (int) $r['view'],
        '_pccc_point' => (float) $r['point'], '_pccc_video' => (string) $r['video'], '_pccc_canonical_url' => (string) $r['canonical_url'],
        '_pccc_robots' => trim("{$r['meta_robots_index']},{$r['meta_robots_follow']}", ','),
    ]);
    if ($id && ($thumb = pccc24_attachment($r['image_url']))) {
        set_post_thumbnail($id, $thumb);
    }
    $n += (bool) $id;
}
WP_CLI::log("articles: $n");

/* Dịch vụ */
$typeMap = [];
foreach ($rows('SELECT * FROM service_types') as $r) {
    $typeMap[$r['id']] = pccc24_term('pccc_service_type', "service_type:{$r['id']}", $r['name'], $r['slug'],
        ['description' => (string) $r['description']], ['_pccc_old_id' => $r['id'], '_pccc_sorted' => (int) $r['sorted'], '_pccc_enabled' => (int) $r['enabled']]);
}
$n = 0;
foreach ($rows('SELECT * FROM services ORDER BY id') as $r) {
    $id = pccc24_upsert("service:{$r['id']}", [
        'post_type' => 'pccc_service', 'post_title' => $r['name'] ?: $r['title'], 'post_name' => $r['slug'],
        'post_content' => pccc24_fix_html($r['content']), 'post_excerpt' => (string) $r['description'], 'menu_order' => (int) $r['sorted'],
    ] + $date($r), [
        '_pccc_old_id' => (int) $r['id'], '_pccc_title' => (string) $r['title'], '_pccc_description' => (string) $r['description'],
        '_pccc_view' => (int) $r['view'], '_pccc_point' => (float) $r['point'], '_pccc_canonical_url' => (string) $r['canonical_url'],
        '_pccc_robots' => trim("{$r['meta_robots_index']},{$r['meta_robots_follow']}", ','),
    ]);
    if (!$id) {
        continue;
    }
    wp_set_object_terms($id, isset($typeMap[$r['service_type_id']]) ? [$typeMap[$r['service_type_id']]] : [], 'pccc_service_type');
    if ($thumb = pccc24_attachment($r['image_url'])) {
        set_post_thumbnail($id, $thumb);
    }
    $n++;
}
WP_CLI::log("services: $n");

/* Trang tĩnh -> page */
$ptMap = [];
foreach ($rows('SELECT * FROM page_types') as $r) {
    $ptMap[$r['id']] = pccc24_term('pccc_page_type', "page_type:{$r['id']}", $r['name'], $r['slug'], ['description' => (string) $r['description']], [
        '_pccc_old_id' => $r['id'], '_pccc_position' => (int) $r['position'], '_pccc_category' => (string) $r['category'],
        '_pccc_sorted' => (int) $r['sorted'], '_pccc_actived' => (int) $r['actived'],
    ]);
}
$n = 0;
foreach ($rows('SELECT * FROM pages ORDER BY id') as $r) {
    $id = pccc24_upsert("page:{$r['id']}", [
        'post_type' => 'page', 'post_title' => $r['name'] ?: $r['title'], 'post_name' => $r['slug'] ?: sanitize_title($r['name'] ?: $r['title']),
        'post_content' => pccc24_fix_html($r['content']), 'post_excerpt' => (string) $r['description'],
    ] + $date($r), [
        '_pccc_old_id' => (int) $r['id'], '_pccc_title' => (string) $r['title'], '_pccc_url' => pccc24_fix_html((string) $r['url']),
    ]);
    if (!$id) {
        continue;
    }
    wp_set_object_terms($id, isset($ptMap[$r['page_type_id']]) ? [$ptMap[$r['page_type_id']]] : [], 'pccc_page_type');
    if ($thumb = pccc24_attachment($r['image_url'])) {
        set_post_thumbnail($id, $thumb);
    }
    $n++;
}
WP_CLI::log("pages: $n");

/* Slider / Chính sách / Hỗ trợ viên */
$simple = [
    'sliders' => ['pccc_slider', fn($r) => ['menu_order' => (int) $r['position']], fn($r) => ['_pccc_url' => pccc24_fix_html((string) $r['url']), '_pccc_position' => (int) $r['position'], '_pccc_enabled' => (int) $r['enabled']]],
    'policies' => ['pccc_policy', fn($r) => ['menu_order' => (int) $r['sorted'], 'post_excerpt' => (string) $r['description']], fn($r) => ['_pccc_url' => pccc24_fix_html((string) $r['url']), '_pccc_position' => (int) $r['position'], '_pccc_enabled' => (int) $r['enabled']]],
    'supports' => ['pccc_support', fn($r) => ['menu_order' => (int) $r['sorted']], fn($r) => ['_pccc_content' => (string) $r['content'], '_pccc_support_type' => (string) $r['support_type']]],
];
foreach ($simple as $table => [$type, $post, $meta]) {
    $n = 0;
    foreach ($rows("SELECT * FROM $table ORDER BY id") as $r) {
        $id = pccc24_upsert("$table:{$r['id']}", ['post_type' => $type, 'post_title' => trim($r['name'])] + $post($r), ['_pccc_old_id' => (int) $r['id']] + $meta($r));
        if ($id && ($thumb = pccc24_attachment($r['image_url']))) {
            set_post_thumbnail($id, $thumb);
        }
        $n += (bool) $id;
    }
    WP_CLI::log("$table: $n");
}

/* Đếm lại term, rewrite theo nhóm trang mới */
wp_defer_term_counting(false);
flush_rewrite_rules();

if ($missing) {
    WP_CLI::warning(count($missing) . ' file ảnh thiếu trong D:\Code\PCCC24\public\uploads: ' . implode(', ', array_keys($missing)));
}
WP_CLI::success('Migrate xong.');
