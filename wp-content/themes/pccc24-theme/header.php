<?php
/**
 * Header template (replicated 100% from app/Views/layouts/app.php)
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

$category_tree = pccc24_get_category_tree();
$service_types = pccc24_get_service_types();
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

    <link href="<?= esc_url(pccc24_theme_asset('css/app.css')) ?>" rel="stylesheet">
    <link href="<?= esc_url(pccc24_theme_asset('static/boxicons/css/boxicons.min.css')) ?>" rel="stylesheet">

    <script>
    window.baseUrl = <?= wp_json_encode(trailingslashit(home_url())) ?>;
    window.PCCC24_CONFIG = {
        baseUrl: <?= wp_json_encode(trailingslashit(home_url())) ?>,
        restUrl: <?= wp_json_encode(untrailingslashit(rest_url('pccc24/v1'))) ?>,
        noImageUrl: <?= wp_json_encode(pccc24_no_image_url()) ?>,
        supports: <?= wp_json_encode(pccc24_get_all_supports(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
    </script>
    <script src="<?= esc_url(pccc24_theme_asset('js/jquery-3.5.1.min.js')) ?>"></script>
    <script src="<?= esc_url(pccc24_theme_asset('js/owl.carousel.min.js')) ?>"></script>
    <script src="<?= esc_url(pccc24_theme_asset('js/theme-app.js')) ?>"></script>

    <style>
    table, table tbody, table tr, table th, table td, table p, table span, table div, table strong, table b {
        background: transparent !important;
        background-color: transparent !important;
    }
    </style>
    <?php wp_head(); ?>
</head>

<body class="bg-slate-50">
    <header class="header pt-2">
        <div class="container-fluid mx-auto">
            <div class="lg-g:flex xs-s:flex-wrap w-full justify-between">
                <div class="logo lg-g:w-2/5 xs-s:w-full flex lg-g:justify-start xs-s:justify-center items-center" style="position: relative; min-height: 50px;">
                    <button type="button" aria-label="Mở menu" class="show-menu navbar-toggler collapsed lg-g:hidden" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; z-index: 50; display: flex; align-items: center; justify-content: center; background: transparent; border: none; padding: 0; cursor: pointer;">
                        <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="20" height="2" fill="#00447C"></rect>
                            <rect y="6" width="20" height="2" fill="#00447C"></rect>
                            <rect y="12" width="20" height="2" fill="#00447C"></rect>
                        </svg>
                    </button>
                    <a href="<?= esc_url(home_url('/')) ?>" class="flex items-center">
                        <img src="<?= esc_url(pccc24_get_setting('logo', false)) ?>" alt="Logo" class="logo-desktop" />
                    </a>
                    <button type="button" onclick="openCartModal()" aria-label="Giỏ hàng" title="Giỏ hàng" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; z-index: 50; display: flex; align-items: center; justify-content: center; background: transparent; border: none; cursor: pointer;">
                        <i class="bx bx-cart text-2xl text-red-pccc"></i>
                        <span class="countCart bg-amber-500 text-white text-xs rounded-full px-1.5 py-0.5 ml-0.5">0</span>
                    </button>
                </div>
                <div class="lg-g:w-3/5 lg-g:block xs-s:hidden">
                    <div class="flex justify-between gap-2">
                        <div class="grid gap-0">
                            <span class="text-sm text-gray-600">Hỗ trợ bán hàng</span>
                            <span class="lg:text-xl md:text-lg xs-s:text-base text-red-pccc">
                                <?= pccc24_get_setting('phone_sale') ?>
                            </span>
                        </div>
                        <div class="grid gap-0">
                            <span class="text-sm text-gray-600">Hỗ trợ kỹ thuật</span>
                            <span class="lg:text-xl md:text-lg xs-s:text-base text-red-pccc">
                                <?= pccc24_get_setting('phone_technique') ?>
                            </span>
                        </div>
                        <div class="grid gap-0">
                            <span class="text-sm text-gray-600">Email báo giá</span>
                            <span class="lg:text-xl md:text-lg xs-s:text-base text-red-pccc">
                                <?= pccc24_get_setting('email') ?>
                            </span>
                        </div>
                    </div>
                    <div class="grid gap-0">
                        <span class="text-lg text-gray-800"><?= pccc24_get_setting('company') ?></span>
                        <span class="text-sm text-gray-600"><?= wp_kses_post(pccc24_get_setting('address', false)) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-red-pccc">
            <div class="container-fluid mx-auto lg-g:block xs-s:hidden">
                <div class="text-white flex gap-2 justify-between h-full">
                    <div class="flex items-center">
                        <div class="bg-rose-600 p-3 text-white flex items-center gap-1 relative has-dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="uppercase">Danh mục sản phẩm</span>
                            <ul class="dropdown-menu dropdown-menu-hide absolute shadow rounded bg-white text-slate-700">
                                <?php foreach ($category_tree as $ct_root) : ?>
                                <li class="dropdown-submenu nav-item-lv2">
                                    <a class="af1" href="<?= esc_url($ct_root->url) ?>">
                                        <span><?= esc_html($ct_root->name) ?></span>
                                        <?php if (!empty($ct_root->childrens)) : ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                        <?php endif; ?>
                                    </a>
                                    <?php if (!empty($ct_root->childrens)) : ?>
                                    <ul class="dropdown-menu dropdown-menu-hide bg-white shadow rounded border-2 border-orange-400">
                                        <?php foreach ($ct_root->childrens as $ct_child) : ?>
                                        <li>
                                            <a class="af2" href="<?= esc_url($ct_child->url) ?>"><?= esc_html($ct_child->name) ?></a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <ul class="flex main-menu whitespace-nowrap">
                            <?php foreach ($service_types as $st) : ?>
                            <li>
                                <a href="<?= esc_url($st->url) ?>"><?= esc_html($st->name) ?></a>
                            </li>
                            <?php endforeach; ?>
                            <li>
                                <a href="<?= esc_url(home_url('/tin-tuc')) ?>">Tin tức</a>
                            </li>
                            <li>
                                <a href="<?= esc_url(home_url('/pages/gioi-thieu')) ?>">Về chúng tôi</a>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center gap-2">
                        <form action="<?= esc_url(home_url('/san-pham/tim-kiem')) ?>" method="get" class="h-[46px] flex items-center px-2 py-1 w-[220px]">
                            <div class="relative text-gray-500 rounded h-full w-full">
                                <input type="text" name="q" value="<?= esc_attr(wp_unslash($_GET['q'] ?? '')) ?>" placeholder="Tìm kiếm" class="form-control pl-2 text-search h-full w-full rounded" />
                                <button type="submit" aria-label="Tìm kiếm" class="absolute top-0 right-0 w-9 h-full text-xl text-gray-500 flex items-center justify-center">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="pb-5">
        <div class="container-fluid mx-auto">
            <div class="w-full">
