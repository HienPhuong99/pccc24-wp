<?php
/** /pages/{slug} */
defined('ABSPATH') || exit;
get_header();
the_post();
$terms  = get_the_terms(get_the_ID(), 'pccc_page_type');
$crumbs = ($terms && !is_wp_error($terms)) ? [[get_term_link($terms[0]), $terms[0]->name]] : [];
get_template_part('template-parts/article', null, ['crumbs' => $crumbs, 'side' => 'sidebar-pages']);
get_footer();
