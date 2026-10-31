<?php
/**
 * Template trang Liên hệ (/contacts)
 * Tương đương app/Views/contacts/index.php
 */

defined('ABSPATH') || exit;

get_header();
?>
<div class="mt-4">
    <div class="crumb-box flex h-10 items-center">
        <ul class="crumb">
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    Trang chủ
                </a>
            </li>
            <li>
                Liên hệ
            </li>
        </ul>
    </div>
</div>
<div class="flex flex-wrap md:flex-nowrap gap-6 mt-2">
    <div class="w-full md:w-[460px] md:min-w-[460px]">
        <b class="text-lg text-gray-800"><?php echo pccc24_get_setting('company'); ?></b>
        <p>Địa chỉ: <span class="adr"><?php echo wp_kses_post(pccc24_get_setting('address', false)); ?></span></p>
        <p>Điện thoại: <span class="tel"><?php echo pccc24_get_setting('phone'); ?></span></p>
        <p>Email: <span class="email"><?php echo pccc24_get_setting('email'); ?></span></p>
        <p>Website: <a class="url fn" href="http://pccc24.com">http://pccc24.com</a></p>
        <p>GPKD: <span class="tel"><?php echo pccc24_get_setting('gpkd'); ?></span></p>
    </div>
    <div class="w-full"></div>
</div>
<?php
get_footer();
