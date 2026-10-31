<?php
/** Đối soát số bản ghi pccc24_db <-> WordPress. php tools/wp-cli.phar eval-file scripts/verify-migration.php */

defined('ABSPATH') || exit;

$src = new mysqli('127.0.0.1', 'root', getenv('PCCC24_SRC_PASS') ?: '000000', 'pccc24_db');
$count = fn($t) => (int) $src->query("SELECT COUNT(*) FROM $t")->fetch_row()[0];
$posts = fn($type) => count(get_posts(['post_type' => $type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_pccc_src']));
$terms = fn($tax) => (int) wp_count_terms(['taxonomy' => $tax, 'hide_empty' => false]);
$meta = fn($key) => (int) $GLOBALS['wpdb']->get_var($GLOBALS['wpdb']->prepare("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->postmeta} WHERE meta_key = %s AND meta_value NOT IN ('', 'a:0:{}')", $key));
$one = fn($sql) => (int) $src->query($sql)->fetch_row()[0];
$specs = $policies = 0;
foreach (get_posts(['post_type' => 'pccc_product', 'numberposts' => -1, 'fields' => 'ids']) as $id) {
    $specs += count((array) get_post_meta($id, '_pccc_specs', true));
    $policies += count((array) get_post_meta($id, '_pccc_policy_ids', true));
}
$withImg = $one("SELECT COUNT(*) FROM products WHERE image_url <> ''");
// Dòng mồ côi (product_id không còn trong products) bị bỏ qua có chủ đích.
$validSpecs = $one('SELECT COUNT(*) FROM product_properties pp JOIN products p ON p.id = pp.product_id');
$validPolicies = $one('SELECT COUNT(*) FROM product_policy_products pp JOIN products p ON p.id = pp.product_id');
// Ảnh trùng file -> 1 attachment; ảnh 404 cả trên site thật -> bỏ qua.
$uploads = wp_upload_dir()['basedir'] . '/pccc24/';
$photoFiles = array_unique(array_map(fn($r) => preg_replace('~^/?uploads/~', '', $r[0]), $src->query('SELECT image_url FROM photos')->fetch_all()));
$validPhotos = count(array_filter($photoFiles, fn($f) => is_file($uploads . $f)));

$checks = [
    ['products', $count('products'), $posts('pccc_product')],
    ['categories', $count('categories'), $terms('pccc_product_cat')],
    ['product_properties (specs, hợp lệ)', $validSpecs, $specs],
    ['products có ảnh -> thumbnail', $withImg, count(get_posts(['post_type' => 'pccc_product', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_thumbnail_id']))],
    ['product_policy_products (hợp lệ)', $validPolicies, $policies],
    ['photos (file duy nhất, tồn tại)', $validPhotos, $meta('_pccc_photo_id')],
    ['articles', $count('articles'), $posts('post')],
    ['services', $count('services'), $posts('pccc_service')],
    ['service_types', $count('service_types'), $terms('pccc_service_type')],
    ['pages', $count('pages'), $posts('page')],
    ['page_types', $count('page_types'), $terms('pccc_page_type')],
    ['sliders', $count('sliders'), $posts('pccc_slider')],
    ['policies', $count('policies'), $posts('pccc_policy')],
    ['supports', $count('supports'), $posts('pccc_support')],
    ['settings', $count('settings'), count(get_option('pccc24_settings', []))],
];
$fail = 0;
foreach ($checks as [$name, $a, $b]) {
    WP_CLI::log(sprintf('%-32s %5d -> %5d  %s', $name, $a, $b, $a === $b ? 'OK' : 'LECH'));
    $fail += $a !== $b;
}
$fail ? WP_CLI::error("$fail mục lệch") : WP_CLI::success('Khớp 100%');
