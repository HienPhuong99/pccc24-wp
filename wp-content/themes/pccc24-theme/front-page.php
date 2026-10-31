<?php
/**
 * Front Page Template (replicated 100% from HomeController::index & app/Views/home/index.php)
 */
defined('ABSPATH') || exit;

get_header();

$category_tree = pccc24_get_category_tree();

// 1. Sliders
$sliders = get_posts([
    'post_type'   => 'pccc_slider',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby'     => ['menu_order' => 'ASC', 'ID' => 'ASC'],
]);

// 2. Policies position = 1 (4 cam kết dưới banner)
$policies_pos1 = get_posts([
    'post_type'   => 'pccc_policy',
    'post_status' => 'publish',
    'numberposts' => -1,
    'meta_key'    => '_pccc_position',
    'meta_value'  => 1,
    'orderby'     => ['menu_order' => 'ASC', 'ID' => 'ASC'],
]);

// 3. Policies position = 2 (Dịch vụ nổi bật - tối đa 6 vòng tròn tím)
$sliders2 = get_posts([
    'post_type'   => 'pccc_policy',
    'post_status' => 'publish',
    'numberposts' => 6,
    'meta_key'    => '_pccc_position',
    'meta_value'  => 2,
    'orderby'     => ['menu_order' => 'ASC', 'ID' => 'ASC'],
]);

// 4. Danh mục trang chủ (popular = 1, order by home_sorted ASC) + tối đa 5 SP mỗi danh mục
$all_cats = get_terms([
    'taxonomy'   => 'pccc_product_cat',
    'hide_empty' => false,
    'meta_query' => [[
        'key'   => '_pccc_popular',
        'value' => 1,
    ]],
]);
if (is_wp_error($all_cats)) {
    $all_cats = [];
}
usort($all_cats, function ($a, $b) {
    $ha = (int) get_term_meta($a->term_id, '_pccc_home_sorted', true);
    $hb = (int) get_term_meta($b->term_id, '_pccc_home_sorted', true);
    if ($ha !== $hb) {
        return $ha <=> $hb;
    }
    $oa = (int) get_term_meta($a->term_id, '_pccc_old_id', true);
    $ob = (int) get_term_meta($b->term_id, '_pccc_old_id', true);
    return $oa <=> $ob;
});

$categories_home = [];
foreach ($all_cats as $ct) {
    $prods = get_posts([
        'post_type'   => 'pccc_product',
        'post_status' => 'publish',
        'numberposts' => 5,
        'orderby'     => ['menu_order' => 'ASC', 'ID' => 'DESC'],
        'tax_query'   => [[
            'taxonomy'         => 'pccc_product_cat',
            'field'            => 'term_id',
            'terms'            => $ct->term_id,
            'include_children' => false,
        ]],
    ]);
    if (!empty($prods)) {
        $ct->products = $prods;
        $ct->url      = get_term_link($ct);
        $categories_home[] = $ct;
    }
}

// 5. Tin tức (4 bài mới nhất)
$articles = get_posts([
    'post_type'   => 'post',
    'post_status' => 'publish',
    'numberposts' => 4,
    'orderby'     => 'ID',
    'order'       => 'DESC',
]);
?>

<div>
    <div class="flex mt-2">
        <div class="border mr-2 hidden lg-g:block lg-g:w-1/5 rounded bg-white">
            <ul class="dropdown-menu">
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
                            <ul class="dropdown-menu w-64 bg-white shadow rounded border-2 border-orange-400">
                                <?php foreach ($ct_root->childrens as $ct_child) : ?>
                                    <li>
                                        <a class="af2" href="<?= esc_url($ct_child->url) ?>">
                                            <?= esc_html($ct_child->name) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="main-slider w-full lg-g:w-4/5">
            <div class="sliders owl-carousel owl-theme owl-has-dot" id="sliders">
                <?php foreach ($sliders as $slider) :
                    $slider_url = trim((string) get_post_meta($slider->ID, '_pccc_url', true));
                    $slider_img = pccc24_post_image_url($slider);
                ?>
                    <?php if ($slider_url !== '') : ?>
                        <a href="<?= esc_url($slider_url) ?>">
                            <img src="<?= esc_url($slider_img) ?>" alt="<?= esc_attr($slider->post_title) ?>" class="ad-slider">
                        </a>
                    <?php else : ?>
                        <img src="<?= esc_url($slider_img) ?>" alt="<?= esc_attr($slider->post_title) ?>" class="ad-slider">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($policies_pos1)) : ?>
    <div class="footer-slide">
        <div class="md:py-10 p-4 xs-s:py-5">
            <div class="mx-auto">
                <div class="owl-carousel owl-theme owl-has-dot" id="service-sliders">
                    <?php foreach ($policies_pos1 as $policy) :
                        $pol_url = trim((string) get_post_meta($policy->ID, '_pccc_url', true));
                        $pol_img = pccc24_post_image_url($policy);
                    ?>
                        <?php if ($pol_url !== '') : ?>
                            <a href="<?= esc_url($pol_url) ?>" class="flex items-center gap-4">
                                <div class="!w-12 min-w-[3rem]">
                                    <img src="<?= esc_url($pol_img) ?>" alt="<?= esc_attr($policy->post_title) ?>" class="w-12">
                                </div>
                                <div>
                                    <p class="text-lg text-gray-800 uppercase"><?= esc_html($policy->post_title) ?></p>
                                    <div class="text-gray-600"><?= wp_kses_post($policy->post_excerpt) ?></div>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="flex items-center gap-4">
                                <div class="!w-12 min-w-[3rem]">
                                    <img src="<?= esc_url($pol_img) ?>" alt="<?= esc_attr($policy->post_title) ?>" class="w-12">
                                </div>
                                <div>
                                    <p class="text-lg text-gray-800 uppercase"><?= esc_html($policy->post_title) ?></p>
                                    <div class="text-gray-600"><?= wp_kses_post($policy->post_excerpt) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($sliders2)) : ?>
    <div class="text-center">
        <h2 class="text-purple-900 md:text-[30px] xs-s:text-2xl">
            Dịch vụ nổi bật
        </h2>
    </div>

    <div>
        <div class="md:py-10 md:p-4 xs-s:p-0 xs-s:py-5">
            <div class="mx-auto">
                <div class="home-support flex flex-wrap justify-evenly md:gap-6 xs-s:gap-3">
                    <?php foreach ($sliders2 as $ct2) :
                        $ct2_url = trim((string) get_post_meta($ct2->ID, '_pccc_url', true)) ?: '#';
                        $ct2_img = pccc24_post_image_url($ct2);
                    ?>
                        <a href="<?= esc_url($ct2_url) ?>" class="flex-1 grid items-start justify-center gap-2">
                            <div class="bg-violet-200 lg:w-[150px] lg:h-[150px] md:w-[120px] xs-s:w-[100px] md:h-[120px] xs-s:h-[100px] xs:w-[110px] xs:h-[110px] support-images rounded-full relative overflow-hidden flex items-center justify-center mx-auto">
                                <img src="<?= esc_url($ct2_img) ?>" alt="<?= esc_attr($ct2->post_title) ?>" class="max-w-[100%] object-contain">
                                <span><?= wp_kses_post($ct2->post_excerpt) ?></span>
                            </div>
                            <div class="text-center">
                                <p class="md:text-base xs-s:text-sm text-gray-800 uppercase"><?= esc_html($ct2->post_title) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="mx-auto">
    <?php foreach ($categories_home as $categoryHome) : ?>
        <div class="category-collection-slider mt-8">
            <div class="border-tab"></div>
            <div class="flex justify-between items-center">
                <div>
                    <label class="popular-label btn-style">
                        <a href="<?= esc_url($categoryHome->url) ?>" class="text">
                            <h2><?= esc_html($categoryHome->name) ?></h2>
                        </a>
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="mt-3 SLIDER-PRODUCT-CT owl-carousel owl-theme">
                <?php foreach ($categoryHome->products as $product) :
                    get_template_part('template-parts/product-card', null, ['product' => $product]);
                endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mx-auto mt-10">
    <div class="border-tab"></div>
    <div class="flex justify-between items-center">
        <div>
            <label class="popular-label btn-style">
                <a href="<?= esc_url(home_url('/tin-tuc')) ?>" class="text">
                    Tin tức
                </a>
                <span></span>
            </label>
        </div>
        <div>
            <ul class="flex gap-3 mr-20"></ul>
        </div>
    </div>
    <div class="grid md:grid-cols-4 xs-s:grid-cols-2 gap-6 mt-5">
        <?php foreach ($articles as $article) : ?>
            <a href="<?= esc_url(get_permalink($article)) ?>" class="col-span-1 grid">
                <div class="md:h-48 xs-s:h-32">
                    <?php if (has_post_thumbnail($article)) : ?>
                        <img src="<?= esc_url(pccc24_post_image_url($article)) ?>" width="600" height="400" alt="<?= esc_attr($article->post_title) ?>" class="h-full w-full object-cover">
                    <?php endif; ?>
                </div>
                <h2 class="mt-4 limit-line limit-line-2 h-[50px] text-blue text-base font-medium">
                    <?= esc_html($article->post_title) ?>
                </h2>
                <div class="limit-line limit-line-2 text-sm text-gray-500">
                    <?= esc_html($article->post_excerpt) ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>
