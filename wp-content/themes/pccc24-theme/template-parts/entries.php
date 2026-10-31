<?php
/**
 * Danh sách bài viết / dịch vụ / trang (vòng lặp chính). $args: title, crumbs, side (template-part sidebar).
 */
defined('ABSPATH') || exit;
?>
<div class="with-side">
    <aside><?php get_template_part('template-parts/' . $args['side']); ?></aside>
    <div>
        <?php get_template_part('template-parts/crumbs', null, ['items' => $args['crumbs']]); ?>
        <div class="page-head"><h1><?= esc_html($args['title']) ?></h1></div>
        <?php if (have_posts()) : ?>
            <ul class="entries">
                <?php while (have_posts()) : the_post();
                    $id  = get_the_ID();
                    $url = trim((string) get_post_meta($id, '_pccc_url', true)) ?: get_permalink();
                    $desc = (string) get_post_meta($id, '_pccc_description', true) ?: get_the_excerpt(); ?>
                    <li><a href="<?= esc_url($url) ?>">
                        <?php if (has_post_thumbnail()) : ?><img src="<?= esc_url(pccc24_post_image_url($id)) ?>" alt="" loading="lazy"><?php else : ?><span></span><?php endif; ?>
                        <span>
                            <h2><?php the_title(); ?></h2>
                            <?php if ($desc) : ?><p><?= esc_html(wp_strip_all_tags($desc)) ?></p><?php endif; ?>
                        </span>
                    </a></li>
                <?php endwhile; ?>
            </ul>
            <?php pccc24_render_pagination(); ?>
        <?php else : ?>
            <div class="panel">Chưa có nội dung.</div>
        <?php endif; ?>
    </div>
</div>
