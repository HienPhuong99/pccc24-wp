<?php
/** /san-pham */
defined('ABSPATH') || exit;
get_header();
get_template_part('template-parts/product-grid', null, [
    'title' => 'Thiết bị phòng cháy chữa cháy', 'crumbs' => [[null, 'Sản phẩm']], 'sort' => true,
]);
get_footer();
