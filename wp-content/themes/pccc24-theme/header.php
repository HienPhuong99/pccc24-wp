<?php
/**
 * Header: SEO meta (giữ từ bản gốc) + Thanh điều hướng kính mờ phong cách iOS (design_handoff_pccc24_ios).
 */
defined('ABSPATH') || exit;

$page_title   = pccc24_get_setting('site_title');
$meta_desc    = pccc24_get_setting('site_description');
$og_title     = $page_title;
$og_url       = home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
$og_image     = pccc24_get_setting('logo_seo', false);
$og_type      = 'website';
$canonical    = $og_url;
$meta_robots  = 'index, follow';
$json_ld_post = null;

if (is_front_page() || is_home()) {
    $canonical = home_url('/');
    $og_url    = $canonical;
} elseif (is_tax('pccc_product_cat')) {
    $term       = get_queried_object();
    $page_title = $term->name;
    $meta_desc  = $term->description ?: $term->name;
    $og_title   = $term->name;
    $og_url     = get_term_link($term);
    $canonical  = $og_url;
    $img_id     = (int) get_term_meta($term->term_id, '_pccc_image', true);
    if ($img_id && ($u = wp_get_attachment_url($img_id))) {
        $og_image = $u;
    }
} elseif (is_tax('pccc_service_type') || is_tax('pccc_page_type')) {
    $term       = get_queried_object();
    $page_title = $term->name;
    $meta_desc  = $term->description ?: $term->name;
    $og_title   = $term->name;
    $og_url     = get_term_link($term);
    $canonical  = $og_url;
} elseif (get_query_var('pccc_view') === 'products') {
    $page_title = 'Sản phẩm phòng cháy chữa cháy';
    $canonical  = home_url('/san-pham');
} elseif (get_query_var('pccc_view') === 'search') {
    $q          = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
    $page_title = 'Tìm kiếm sản phẩm ' . $q;
    $canonical  = home_url('/san-pham/tim-kiem');
} elseif (get_query_var('pccc_view') === 'services') {
    $page_title = 'Dịch vụ phòng cháy chữa cháy';
    $canonical  = home_url('/dich-vu');
} elseif (get_query_var('pccc_view') === 'articles') {
    $page_title = 'Tin tức phòng cháy chữa cháy';
    $canonical  = home_url('/tin-tuc');
} elseif (get_query_var('pccc_view') === 'contacts') {
    $page_title = 'Liên hệ';
    $canonical  = home_url('/contacts');
} elseif (is_singular()) {
    $post       = get_queried_object();
    $seo_title  = trim((string) get_post_meta($post->ID, '_pccc_title', true));
    $seo_desc   = trim((string) get_post_meta($post->ID, '_pccc_description', true));
    $page_title = $seo_title !== '' ? $seo_title : $post->post_title;
    $meta_desc  = $seo_desc !== '' ? $seo_desc : wp_strip_all_tags($post->post_excerpt ?: $post->post_title);
    $og_title   = $page_title;
    $og_url     = get_permalink($post);
    $canonical  = trim((string) get_post_meta($post->ID, '_pccc_canonical_url', true)) ?: $og_url;
    $robots_raw = trim((string) get_post_meta($post->ID, '_pccc_robots', true));
    if ($robots_raw !== '') {
        $parts       = array_map('trim', explode(',', $robots_raw));
        $meta_robots = implode(', ', array_filter($parts)) ?: 'index, follow';
    }
    if (has_post_thumbnail($post)) {
        $og_image = pccc24_post_image_url($post);
    }
    if ($post->post_type === 'post') {
        $og_type      = 'article';
        $json_ld_post = $post;
    }
}

$cur_view = get_query_var('pccc_view');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= esc_html($page_title) ?></title>
    <link rel="canonical" href="<?= esc_url($canonical) ?>" />
    <link rel="icon" href="<?= esc_url(pccc24_theme_asset('images/favicon.ico')) ?>" type="image/x-icon" />

    <meta name="description" content="<?= esc_attr($meta_desc) ?>" />
    <meta property="og:type" content="<?= esc_attr($og_type) ?>" />
    <meta property="og:title" content="<?= esc_attr($og_title) ?>" />
    <meta property="og:url" content="<?= esc_url($og_url) ?>" />
    <meta property="og:description" content="<?= esc_attr($meta_desc) ?>" />
    <?php if ($og_image) : ?>
    <meta property="og:image" content="<?= esc_url($og_image) ?>" />
    <?php endif; ?>

    <?php if ($json_ld_post) :
        $headline = preg_replace('/\s+/', ' ', trim($json_ld_post->post_title));
        $desc_ld  = preg_replace('/\s+/', ' ', trim($meta_desc));
        $img_ld   = has_post_thumbnail($json_ld_post) ? pccc24_post_image_url($json_ld_post) : pccc24_no_image_url();
    ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": <?= wp_json_encode($headline, JSON_UNESCAPED_UNICODE) ?>,
        "description": <?= wp_json_encode($desc_ld, JSON_UNESCAPED_UNICODE) ?>,
        "image": <?= wp_json_encode($img_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
        "datePublished": "<?= esc_js(get_the_date('c', $json_ld_post)) ?>",
        "dateModified": "<?= esc_js(get_the_modified_date('c', $json_ld_post)) ?>",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= esc_url(get_permalink($json_ld_post)) ?>"
        },
        "author": {
            "@type": "Person",
            "name": "Nguyễn Phú Toàn",
            "url": "<?= esc_url(home_url('/pages/tac-gia-nguyen-phu-toan')) ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "PCCC24",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= esc_url(pccc24_get_setting('logo', false)) ?>"
            }
        }
    }
    </script>
    <?php endif; ?>

    <meta name="robots" content="<?= esc_attr($meta_robots) ?>">
    <meta content="1 days" name="revisit-after">
    <meta name="Generator" content="pccc24.com - Copyright 2014 All rights reserved.">
    <meta content="global" name="distribution">
    <meta content="general" name="rating">
    <meta name="author" content="PCCC24" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= esc_url(pccc24_theme_asset('css/site.css')) ?>?v=6" rel="stylesheet">
    <link href="<?= esc_url(pccc24_theme_asset('css/ios-sheet.css')) ?>?v=4" rel="stylesheet">
    <script>window.PCCC24_CONFIG = <?= wp_json_encode(['restUrl' => untrailingslashit(rest_url('pccc24/v1')), 'noImageUrl' => pccc24_no_image_url()]) ?>;</script>
    <script src="<?= esc_url(pccc24_theme_asset('js/site.js')) ?>?v=5" defer></script>
    <script src="<?= esc_url(pccc24_theme_asset('js/ios-sheet.js')) ?>?v=4" defer></script>
    <?php wp_head(); ?>
</head>

<body>
<a class="sr-only" href="#main">Bỏ qua menu</a>
<header class="ios-header">
    <div class="ios-header-inner">
        <div class="ios-brand-group">
            <button type="button" class="menu-btn" data-open="drawer" aria-label="Mở menu"><?= pccc24_icon('menu') ?></button>
            <a class="logo" href="<?= esc_url(home_url('/')) ?>">
                <img data-fb="1" src="<?= esc_url(pccc24_get_setting('logo', false)) ?>" alt="PCCC24 - Hoàng Quân Phát" width="145" height="40">
                <span class="logo-fallback"><b class="logo-fb-red">PCCC24</b><span class="logo-fb-sub">Hoàng Quân Phát</span></span>
            </a>
        </div>

        <nav class="ios-nav" aria-label="Menu chính">
            <details class="nav-cats">
                <summary class="nav-pill<?= (is_front_page() || $cur_view === 'products' || is_tax('pccc_product_cat') || is_singular('pccc_product')) ? ' active' : '' ?>">Sản phẩm</summary>
                <?php get_template_part('template-parts/cat-list'); ?>
            </details>
            <a class="nav-pill<?= is_tax('pccc_service_type', 'thi-cong-lap-dat-he-thong-pccc') ? ' active' : '' ?>" href="<?= esc_url(home_url('/dich-vu/thi-cong-lap-dat-he-thong-pccc')) ?>">Thi công</a>
            <a class="nav-pill<?= is_tax('pccc_service_type', 'bao-tri-sua-chua-he-thong-pccc') ? ' active' : '' ?>" href="<?= esc_url(home_url('/dich-vu/bao-tri-sua-chua-he-thong-pccc')) ?>">Bảo trì</a>
            <a class="nav-pill<?= is_tax('pccc_service_type', 'du-an') ? ' active' : '' ?>" href="<?= esc_url(home_url('/dich-vu/du-an')) ?>">Dự án</a>
            <a class="nav-pill<?= ($cur_view === 'articles' || is_singular('post')) ? ' active' : '' ?>" href="<?= esc_url(home_url('/tin-tuc')) ?>">Tin tức</a>
            <a class="nav-pill<?= $cur_view === 'contacts' ? ' active' : '' ?>" href="<?= esc_url(home_url('/contacts')) ?>">Liên hệ</a>
        </nav>

        <div class="ios-header-actions">
        <form class="search" action="<?= esc_url(home_url('/san-pham/tim-kiem')) ?>" method="get" role="search">
            <?= pccc24_icon('search') ?>
            <label class="sr-only" for="q">Tìm thiết bị</label>
            <input id="q" type="search" name="q" value="<?= esc_attr(wp_unslash($_GET['q'] ?? '')) ?>" placeholder="Tìm thiết bị">
        </form>

        <button type="button" class="btn-quote-pill" data-quote-open>Báo giá</button>
        </div>
    </div>
</header>
<main id="main">
    <div class="wrap">
