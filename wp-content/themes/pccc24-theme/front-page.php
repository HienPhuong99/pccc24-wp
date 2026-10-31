<?php
/**
 * Trang chủ: danh mục + slider + thẻ báo giá, cam kết, nhu cầu, kệ sản phẩm theo danh mục, tin tức.
 * Dữ liệu giống HomeController::index của bản CI4.
 */
defined('ABSPATH') || exit;
get_header();

$by_order = ['menu_order' => 'ASC', 'ID' => 'ASC'];
$sliders  = get_posts(['post_type' => 'pccc_slider', 'numberposts' => -1, 'orderby' => $by_order]);
$policies = fn($pos, $n) => get_posts(['post_type' => 'pccc_policy', 'numberposts' => $n, 'meta_key' => '_pccc_position', 'meta_value' => $pos, 'orderby' => $by_order]);
$promises = $policies(1, -1);
$needs    = $policies(2, 6);

// Danh mục trang chủ: popular = 1, theo home_sorted; mỗi kệ 5 sản phẩm.
$cats = get_terms(['taxonomy' => 'pccc_product_cat', 'hide_empty' => false, 'meta_key' => '_pccc_popular', 'meta_value' => 1]);
$cats = is_wp_error($cats) ? [] : $cats;
usort($cats, fn($a, $b) => [(int) get_term_meta($a->term_id, '_pccc_home_sorted', true), (int) get_term_meta($a->term_id, '_pccc_old_id', true)]
    <=> [(int) get_term_meta($b->term_id, '_pccc_home_sorted', true), (int) get_term_meta($b->term_id, '_pccc_old_id', true)]);

$articles = get_posts(['post_type' => 'post', 'numberposts' => 4, 'orderby' => 'ID', 'order' => 'DESC']);
$lead     = array_shift($articles);
?>

<h1 class="sr-only"><?= pccc24_get_setting('site_title') ?></h1>

<section class="hero">
    <?php get_template_part('template-parts/cat-list'); ?>
    <div>
        <div class="slides" tabindex="0" aria-label="Chương trình nổi bật">
            <?php foreach ($sliders as $s) :
                $url = trim((string) get_post_meta($s->ID, '_pccc_url', true));
                $img = '<img src="' . esc_url(pccc24_post_image_url($s)) . '" alt="' . esc_attr($s->post_title) . '">';
                echo $url ? '<a href="' . esc_url($url) . '">' . $img . '</a>' : '<div>' . $img . '</div>';
            endforeach; ?>
        </div>
        <div class="slide-dots"></div>
    </div>
    <?php get_template_part('template-parts/quote-tag'); ?>
</section>

<?php if ($promises) : ?>
    <ul class="promises">
        <?php foreach ($promises as $p) : ?>
            <li>
                <img src="<?= esc_url(pccc24_post_image_url($p)) ?>" alt="">
                <div><b><?= esc_html($p->post_title) ?></b><?php if ($p->post_excerpt) : ?><span><?= esc_html(wp_strip_all_tags($p->post_excerpt)) ?></span><?php endif; ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($needs) : ?>
    <section class="section">
        <div class="section-head"><h2>Bạn đang cần</h2></div>
        <div class="services">
            <?php foreach ($needs as $n) : ?>
                <a href="<?= esc_url(trim((string) get_post_meta($n->ID, '_pccc_url', true)) ?: '#') ?>">
                    <img src="<?= esc_url(pccc24_post_image_url($n)) ?>" alt="" loading="lazy">
                    <span><?= esc_html($n->post_title) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php foreach ($cats as $cat) :
    $products = get_posts([
        'post_type' => 'pccc_product', 'numberposts' => 5, 'orderby' => ['menu_order' => 'ASC', 'ID' => 'DESC'],
        'tax_query' => [['taxonomy' => 'pccc_product_cat', 'terms' => $cat->term_id, 'include_children' => false]],
    ]);
    if (!$products) {
        continue;
    } ?>
    <section class="section">
        <div class="section-head">
            <h2><?= esc_html($cat->name) ?></h2>
            <a href="<?= esc_url(get_term_link($cat)) ?>">Xem tất cả <?= (int) $cat->count ?> sản phẩm</a>
        </div>
        <div class="grid-products row-products">
            <?php foreach ($products as $product) {
                get_template_part('template-parts/product-card', null, ['product' => $product]);
            } ?>
        </div>
    </section>
<?php endforeach; ?>

<?php if ($lead) : ?>
    <section class="section">
        <div class="section-head">
            <h2>Kiến thức PCCC</h2>
            <a href="<?= esc_url(home_url('/tin-tuc')) ?>">Tất cả bài viết</a>
        </div>
        <div class="news">
            <a class="news-lead" href="<?= esc_url(get_permalink($lead)) ?>">
                <img src="<?= esc_url(pccc24_post_image_url($lead)) ?>" alt="" loading="lazy">
                <h3><?= esc_html($lead->post_title) ?></h3>
                <time datetime="<?= esc_attr(get_the_date('c', $lead)) ?>"><?= esc_html(pccc24_time_string($lead)) ?></time>
            </a>
            <ul class="news-list">
                <?php foreach ($articles as $a) : ?>
                    <li><a href="<?= esc_url(get_permalink($a)) ?>">
                        <img src="<?= esc_url(pccc24_post_image_url($a)) ?>" alt="" loading="lazy">
                        <span><?= esc_html($a->post_title) ?><time datetime="<?= esc_attr(get_the_date('c', $a)) ?>"><?= esc_html(pccc24_time_string($a)) ?></time></span>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>
