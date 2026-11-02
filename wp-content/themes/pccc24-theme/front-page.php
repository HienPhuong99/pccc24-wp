<?php
/**
 * Trang chủ theo thiết kế PCCC24 iOS Handoff:
 * Large Title, Category Chips, Hero (Banner bo tròn + Thẻ báo giá), 4 Cam kết,
 * Bạn đang cần (4:5 cards), Sản phẩm nổi bật (iOS Segmented Control), Tin Tức PCCC.
 */
defined('ABSPATH') || exit;
get_header();

$by_order = ['menu_order' => 'ASC', 'ID' => 'ASC'];
$sliders  = get_posts(['post_type' => 'pccc_slider', 'numberposts' => -1, 'orderby' => $by_order]);
$policies = fn($pos, $n) => get_posts(['post_type' => 'pccc_policy', 'numberposts' => $n, 'meta_key' => '_pccc_position', 'meta_value' => $pos, 'orderby' => $by_order]);
$promises = $policies(1, 4);
$needs    = $policies(2, 6);

$cat_tree = pccc24_get_category_tree();

// Danh mục trang chủ: popular = 1, theo home_sorted
$cats = get_terms(['taxonomy' => 'pccc_product_cat', 'hide_empty' => false, 'meta_key' => '_pccc_popular', 'meta_value' => 1]);
$cats = is_wp_error($cats) ? [] : $cats;
usort($cats, fn($a, $b) => [(int) get_term_meta($a->term_id, '_pccc_home_sorted', true), (int) get_term_meta($a->term_id, '_pccc_old_id', true)]
    <=> [(int) get_term_meta($b->term_id, '_pccc_home_sorted', true), (int) get_term_meta($b->term_id, '_pccc_old_id', true)]);

$featured_tabs = [];
foreach ($cats as $cat) {
    $prods = get_posts([
        'post_type'   => 'pccc_product',
        'numberposts' => 4,
        'orderby'     => ['menu_order' => 'ASC', 'ID' => 'DESC'],
        'tax_query'   => [['taxonomy' => 'pccc_product_cat', 'terms' => $cat->term_id, 'include_children' => false]],
    ]);
    if ($prods) {
        $featured_tabs[] = [
            'term'     => $cat,
            'products' => $prods,
        ];
    }
}

$promise_fallbacks = [
    'Giao trong ngày hoặc dưới 24h',
    'Qua điện thoại và Zalo, cả ngoài giờ',
    'Có tem kiểm định, đổi trả theo yêu cầu',
    'Thi công, bảo trì theo quy định hiện hành',
];

$articles = get_posts(['post_type' => 'post', 'numberposts' => 4, 'orderby' => 'ID', 'order' => 'DESC']);
$lead     = array_shift($articles);
?>

<div class="home-title-block">
    <div class="home-eyebrow"><?= pccc24_get_setting('company') ?></div>
    <h1 class="home-title">Thiết bị phòng cháy chữa cháy</h1>
    <p class="home-subtitle"><?= pccc24_get_setting('site_title') ?></p>
</div>

<?php if ($cat_tree) : ?>
    <div class="cat-chips" aria-label="Danh mục thiết bị">
        <?php foreach ($cat_tree as $root) : ?>
            <a class="cat-chip" href="<?= esc_url($root->url) ?>">
                <span><?= esc_html($root->name) ?></span>
                <?php if (!empty($root->childrens)) : ?>
                    <small><?= count($root->childrens) ?></small>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="hero">
    <div class="hero-banner">
        <div class="slides" tabindex="0" aria-label="Chương trình nổi bật">
            <?php foreach ($sliders as $i => $s) :
                $url  = trim((string) get_post_meta($s->ID, '_pccc_url', true));
                $prio = $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"';
                $inner = '<img src="' . esc_url(pccc24_post_image_url($s, 'hero')) . '" alt="' . esc_attr($s->post_title) . '" width="1100" height="400" ' . $prio . ' data-fb="1">'
                    . '<span class="img-fb"><span>' . esc_html($s->post_title) . '</span></span>';
                echo $url ? '<a class="slide" href="' . esc_url($url) . '">' . $inner . '</a>' : '<div class="slide">' . $inner . '</div>';
            endforeach; ?>
        </div>
        <div class="slide-dots"></div>
    </div>
    <?php get_template_part('template-parts/quote-tag'); ?>
</section>

<?php if ($promises) : ?>
    <ul class="promises">
        <?php foreach (array_values($promises) as $idx => $p) :
            $desc = trim(wp_strip_all_tags((string) $p->post_excerpt));
            if ($desc === '') {
                $desc = $promise_fallbacks[$idx] ?? '';
            }
        ?>
            <li>
                <div class="promise-icon">
                    <img src="<?= esc_url(pccc24_post_image_url($p)) ?>" alt="" width="28" height="28" loading="lazy" decoding="async" data-fb="1">
                    <span class="img-fb"></span>
                </div>
                <div>
                    <b class="promise-title"><?= esc_html($p->post_title) ?></b>
                    <?php if ($desc !== '') : ?>
                        <span class="promise-desc"><?= esc_html($desc) ?></span>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($needs) : ?>
    <section class="section">
        <div class="section-head"><h2>Bạn đang cần</h2></div>
        <div class="services">
            <?php foreach ($needs as $n) : ?>
                <a class="service-card" href="<?= esc_url(trim((string) get_post_meta($n->ID, '_pccc_url', true)) ?: '#') ?>">
                    <img src="<?= esc_url(pccc24_post_image_url($n)) ?>" alt="" loading="lazy" decoding="async" width="280" height="350" data-fb="1">
                    <span class="img-fb"><span><?= esc_html($n->post_title) ?></span></span>
                    <span class="service-label"><?= esc_html($n->post_title) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($featured_tabs) :
    $first_tab = $featured_tabs[0]['term'];
?>
    <section class="section">
        <div class="section-head">
            <h2>Sản phẩm nổi bật</h2>
            <a id="featured-more-link" href="<?= esc_url(get_term_link($first_tab)) ?>">Xem tất cả <span id="featured-more-count"><?= (int) $first_tab->count ?></span> sản phẩm ›</a>
        </div>

        <div class="seg-wrap">
            <div class="seg-control" role="tablist" aria-label="Danh mục sản phẩm nổi bật">
                <?php foreach ($featured_tabs as $idx => $tab) :
                    $cat = $tab['term'];
                ?>
                    <button
                        type="button"
                        class="seg-btn<?= $idx === 0 ? ' is-active' : '' ?>"
                        role="tab"
                        aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>"
                        data-seg-tab="cat-pane-<?= (int) $cat->term_id ?>"
                        data-more-url="<?= esc_url(get_term_link($cat)) ?>"
                        data-more-count="<?= (int) $cat->count ?>"
                    ><?= esc_html($cat->name) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php foreach ($featured_tabs as $idx => $tab) :
            $cat = $tab['term'];
        ?>
            <div
                class="grid-products row-products featured-pane"
                id="cat-pane-<?= (int) $cat->term_id ?>"
                role="tabpanel"
                <?= $idx === 0 ? '' : 'hidden' ?>
            >
                <?php foreach ($tab['products'] as $product) {
                    get_template_part('template-parts/product-card', null, ['product' => $product]);
                } ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($lead) : ?>
    <section class="section">
        <div class="section-head">
            <h2>Tin Tức PCCC</h2>
            <a href="<?= esc_url(home_url('/tin-tuc')) ?>">Tất cả bài viết ›</a>
        </div>
        <div class="news">
            <a class="news-lead" href="<?= esc_url(get_permalink($lead)) ?>">
                <div class="news-lead-meta">MỚI NHẤT · <time datetime="<?= esc_attr(get_the_date('c', $lead)) ?>"><?= esc_html(pccc24_time_string($lead)) ?></time></div>
                <h3><?= esc_html($lead->post_title) ?></h3>
            </a>
            <ul class="news-list">
                <?php foreach ($articles as $a) : ?>
                    <li>
                        <a href="<?= esc_url(get_permalink($a)) ?>">
                            <div class="news-thumb">
                                <img src="<?= esc_url(pccc24_post_image_url($a)) ?>" alt="" loading="lazy" decoding="async" width="76" height="56" data-fb="1">
                                <span class="img-fb"></span>
                            </div>
                            <div class="news-info">
                                <span class="news-item-title"><?= esc_html($a->post_title) ?></span>
                                <time datetime="<?= esc_attr(get_the_date('c', $a)) ?>"><?= esc_html(pccc24_time_string($a)) ?></time>
                            </div>
                            <span class="news-chev" aria-hidden="true">›</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>
