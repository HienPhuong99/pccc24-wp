<?php
/**
 * Template 404 Not Found
 */

defined('ABSPATH') || exit;

get_header();
?>
<div class="py-12 text-center">
    <h1 class="text-3xl font-semibold text-red-pccc mb-4">404 - Không tìm thấy trang</h1>
    <p class="text-gray-600 mb-6">Trang bạn đang tìm kiếm không tồn tại hoặc đã được chuyển đi.</p>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block bg-red-pccc text-white px-6 py-2 rounded hover:opacity-90">
        Quay về Trang chủ
    </a>
</div>
<?php
get_footer();
