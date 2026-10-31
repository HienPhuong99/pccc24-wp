<?php
/**
 * Product Card Template Part (replicated from categories/show.php & home/index.php)
 * Expects $args['product'] (WP_Post) or global $post.
 */
defined('ABSPATH') || exit;

$prod   = $args['product'] ?? get_post();
if (!$prod) {
    return;
}
$old_id = (int) pccc24_old_id($prod);
$price  = (int) get_post_meta($prod->ID, '_pccc_price', true);
$img    = pccc24_post_image_url($prod);
$url    = get_permalink($prod);
?>
<div class="rounded h-72 pt-2 bg-white shadow relative itemProduct">
    <a class="w-full" href="<?= esc_url($url) ?>">
        <div class="h-44 w-full mx-auto flex items-center justify-center">
            <img class="h-full !w-auto" src="<?= esc_url($img) ?>" alt="<?= esc_attr($prod->post_title) ?>" />
        </div>
        <div class="grid gap-1 text-center px-2 mt-3">
            <span class="limit-line limit-line-2 h-12 text-base">
                <?= esc_html($prod->post_title) ?>
            </span>
            <span class="text-base text-amber-600">
                <?php if ($price > 0) : ?>
                    <span><?= esc_html(pccc24_price_format($price)) ?>đ</span>
                <?php else : ?>
                    <span>Liên hệ</span>
                <?php endif; ?>
            </span>
        </div>
    </a>
    <div class="absolute z-10 previewProduct">
        <div class="flex gap-3 items-center justify-center">
            <span onclick="previewProduct(<?= $old_id ?>)" title="Xem nhanh" class="rounded p-2 bg-violet-500 shadow cursor-pointer text-white hover:bg-violet-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
            <span onclick="addCart(<?= $old_id ?>)" title="Thêm vào giỏ hàng" class="rounded p-2 bg-violet-500 shadow cursor-pointer text-white hover:bg-violet-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </span>
        </div>
    </div>
</div>
