<?php
/** /dich-vu và /dich-vu/{loai} */
defined('ABSPATH') || exit;
get_header();
$type = is_tax('pccc_service_type') ? get_queried_object() : null;
get_template_part('template-parts/entries', null, [
    'title'  => $type ? $type->name : 'Dịch vụ phòng cháy chữa cháy',
    'crumbs' => $type ? [[home_url('/dich-vu'), 'Dịch vụ'], [null, $type->name]] : [[null, 'Dịch vụ']],
    'side'   => 'sidebar-services',
]);
get_footer();
