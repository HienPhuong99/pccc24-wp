<?php
/**
 * Ô sản phẩm theo PCCC24 iOS Handoff. $args['product'] (WP_Post) hoặc post hiện tại.
 */
defined('ABSPATH') || exit;
$prod = $args['product'] ?? get_post();
if (!$prod) {
    return;
}
$price = (int) get_post_meta($prod->ID, '_pccc_price', true);
?>
<article class="product">
    <div class="product-img">
        <img src="<?= esc_url(pccc24_post_image_url($prod, 'card')) ?>" alt="<?= esc_attr($prod->post_title) ?>" loading="lazy" decoding="async" width="300" height="300" data-fb="1">
        <span class="img-fb"><span><?= esc_html($prod->post_title) ?></span></span>
    </div>
    <a class="product-link" href="<?= esc_url(get_permalink($prod)) ?>"><h3><?= esc_html($prod->post_title) ?></h3></a>
    <div class="product-foot">
        <span class="product-price"><?= $price > 0 ? esc_html(pccc24_price_format($price)) . 'đ' : 'Liên hệ' ?></span>
        <button type="button" class="quick" data-quick="<?= (int) pccc24_old_id($prod) ?>">Xem nhanh</button>
    </div>
</article>
