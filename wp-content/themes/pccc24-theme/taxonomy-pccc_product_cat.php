<?php
/** /san-pham/{danh-muc} */
defined('ABSPATH') || exit;
get_header();
$cat    = get_queried_object();
$crumbs = [[home_url('/san-pham'), 'Sản phẩm']];
if ($cat->parent && ($parent = get_term($cat->parent, 'pccc_product_cat')) && !is_wp_error($parent)) {
    $crumbs[] = [get_term_link($parent), $parent->name];
}
$crumbs[] = [null, $cat->name];
get_template_part('template-parts/product-grid', null, [
    'title' => $cat->name, 'crumbs' => $crumbs, 'sort' => true,
    'after' => (string) get_term_meta($cat->term_id, '_pccc_content', true),
]);
get_footer();
