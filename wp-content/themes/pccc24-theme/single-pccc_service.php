<?php
/** /dich-vu/{slug}/{id} */
defined('ABSPATH') || exit;
get_header();
the_post();
$crumbs = [[home_url('/dich-vu'), 'Dịch vụ']];
$terms  = get_the_terms(get_the_ID(), 'pccc_service_type');
if ($terms && !is_wp_error($terms)) {
    $crumbs[] = [get_term_link($terms[0]), $terms[0]->name];
}
get_template_part('template-parts/article', null, ['crumbs' => $crumbs, 'side' => 'sidebar-services', 'date' => true]);
get_footer();
