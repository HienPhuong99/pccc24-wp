<?php
/**
 * Nội dung một bài (tin tức / dịch vụ / trang). $args: crumbs, side, date (bool).
 */
defined('ABSPATH') || exit;
?>
<div class="with-side">
    <aside><?php get_template_part('template-parts/' . $args['side']); ?></aside>
    <article>
        <?php get_template_part('template-parts/crumbs', null, ['items' => $args['crumbs']]); ?>
        <div class="panel">
            <h1><?php the_title(); ?></h1>
            <?php if (!empty($args['date'])) : ?>
                <p class="entry-meta">Cập nhật <time datetime="<?= esc_attr(get_the_modified_date('c')) ?>"><?= esc_html(get_the_modified_date('d/m/Y')) ?></time></p>
            <?php endif; ?>
            <div class="prose"><?php the_content(); ?></div>
        </div>
    </article>
</div>
