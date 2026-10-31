<?php
/** /tin-tuc */
defined('ABSPATH') || exit;
get_header();
get_template_part('template-parts/entries', null, [
    'title' => 'Kiến thức & tin tức PCCC', 'crumbs' => [[null, 'Tin tức']], 'side' => 'sidebar-articles',
]);
get_footer();
