<?php
/** 404 */
defined('ABSPATH') || exit;
get_header();
?>
<div class="panel" style="max-width:640px;margin:40px auto">
    <h1>Không có trang này</h1>
    <p>Đường dẫn có thể đã đổi. Tìm thiết bị bằng ô tìm kiếm phía trên, xem <a href="<?= esc_url(home_url('/san-pham')) ?>">toàn bộ sản phẩm</a>, hoặc gọi <a href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_get_setting('phone_sale') ?></a>.</p>
</div>
<?php get_footer(); ?>
