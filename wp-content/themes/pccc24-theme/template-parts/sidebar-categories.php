<?php
/** Sidebar: danh mục + sản phẩm nổi bật. */
defined('ABSPATH') || exit;
?>
<div class="side-block">
    <h2>Danh mục</h2>
    <?php get_template_part('template-parts/cat-list'); ?>
</div>
<div class="side-block side-products">
    <h2>Sản phẩm nổi bật</h2>
    <?php foreach (pccc24_get_popular_products(5) as $p) : ?>
        <a href="<?= esc_url(get_permalink($p)) ?>"><img src="<?= esc_url(pccc24_post_image_url($p)) ?>" alt="" loading="lazy"><span><?= esc_html($p->post_title) ?></span></a>
    <?php endforeach; ?>
</div>
