<?php
/** /tin-tuc/{slug}/{id} */
defined('ABSPATH') || exit;
get_header();
the_post();
get_template_part('template-parts/article', null, [
    'crumbs' => [[home_url('/tin-tuc'), 'Tin tức']], 'side' => 'sidebar-articles', 'date' => true,
]);
get_footer();
