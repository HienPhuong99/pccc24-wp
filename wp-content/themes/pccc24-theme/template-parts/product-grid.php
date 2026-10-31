<?php
/**
 * Trang danh sách sản phẩm dùng chung: tất cả / danh mục / tìm kiếm.
 * $args: title, crumbs [[url, name]...], sort (bool), empty (string), after (HTML).
 */
defined('ABSPATH') || exit;
$sorted = (int) ($_GET['sorted'] ?? 0);
$sorts  = [0 => 'Mặc định', 1 => 'Tên A → Z', 2 => 'Tên Z → A', 3 => 'Giá tăng dần', 4 => 'Giá giảm dần'];
?>
<div class="with-side">
    <aside><?php get_template_part('template-parts/sidebar-categories'); ?></aside>
    <div>
        <?php get_template_part('template-parts/crumbs', null, ['items' => $args['crumbs'] ?? []]); ?>
        <div class="page-head">
            <h1><?= esc_html($args['title']) ?></h1>
            <?php if (!empty($args['sort']) && have_posts()) : ?>
                <form class="sort" method="get">
                    <label for="sorted">Sắp xếp</label>
                    <select id="sorted" name="sorted" onchange="this.form.submit()">
                        <?php foreach ($sorts as $k => $label) : ?>
                            <option value="<?= $k ?>"<?php selected($sorted, $k); ?>><?= esc_html($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit">Lọc</button></noscript>
                </form>
            <?php endif; ?>
        </div>
        <?php if (have_posts()) : ?>
            <div class="grid-products">
                <?php while (have_posts()) : the_post();
                    get_template_part('template-parts/product-card');
                endwhile; ?>
            </div>
            <?php pccc24_render_pagination(); ?>
        <?php else : ?>
            <div class="panel"><?= esc_html($args['empty'] ?? 'Chưa có sản phẩm.') ?> Gọi <a href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_get_setting('phone_sale') ?></a> để được tư vấn thiết bị phù hợp.</div>
        <?php endif; ?>
        <?php if (!empty($args['after'])) : ?>
            <div class="panel prose" style="margin-top:32px;max-width:none"><?= wp_kses_post($args['after']) ?></div>
        <?php endif; ?>
    </div>
</div>
