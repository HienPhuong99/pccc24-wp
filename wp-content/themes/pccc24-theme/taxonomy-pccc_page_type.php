<?php
/** /pages/type/{slug} và /{nhom-trang} */
defined('ABSPATH') || exit;
get_header();
$type = get_queried_object();
get_template_part('template-parts/entries', null, ['title' => $type->name, 'crumbs' => [[null, $type->name]], 'side' => 'sidebar-pages']);
get_footer();
