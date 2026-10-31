<?php
/** /san-pham/tim-kiem?q= */
defined('ABSPATH') || exit;
get_header();
$q = sanitize_text_field(wp_unslash($_GET['q'] ?? get_search_query()));
get_template_part('template-parts/product-grid', null, [
    'title'  => $q !== '' ? "Kết quả cho “{$q}”" : 'Tìm sản phẩm',
    'crumbs' => [[home_url('/san-pham'), 'Sản phẩm'], [null, 'Tìm kiếm']],
    'empty'  => "Không có sản phẩm nào khớp “{$q}”. Thử từ khoá ngắn hơn, ví dụ “bình bột”, “đầu báo”.",
]);
get_footer();
