<?php
/** Sidebar tin nổi bật. */
defined('ABSPATH') || exit;
?>
<div class="side-block side-products">
    <h2>Đọc nhiều</h2>
    <?php foreach (pccc24_get_popular_articles(5) as $a) : ?>
        <a href="<?= esc_url(get_permalink($a)) ?>"><img src="<?= esc_url(pccc24_post_image_url($a)) ?>" alt="" loading="lazy" style="object-fit:cover"><span><?= esc_html($a->post_title) ?></span></a>
    <?php endforeach; ?>
</div>
