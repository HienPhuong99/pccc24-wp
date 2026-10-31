<?php
/**
 * Sidebar Categories & Popular Products (replicated from app/Views/categories/left.php)
 */
defined('ABSPATH') || exit;

$category_tree    = pccc24_get_category_tree();
$products_popular = pccc24_get_popular_products(5);
?>
<div class="border-tab"></div>
<div class="flex justify-between items-center mb-2">
    <div class="w-full">
        <label class="popular-label popular-label-icon w-full">
            <i class="bx bx-menu"></i>
            <span class="text">Danh mục</span>
        </label>
    </div>
</div>
<div class="border rounded bg-white">
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

<div class="mt-8 md:block xs-s:hidden">
    <div class="border-tab"></div>
    <div class="flex justify-between items-center mb-2">
        <div class="w-full">
            <label class="popular-label popular-label-icon w-full">
                <i class="bx bxs-hot"></i>
                <span class="text">Sản phẩm nổi bật</span>
            </label>
        </div>
    </div>
    <div>
        <?php foreach ($products_popular as $ph) :
            $ph_price = (int) get_post_meta($ph->ID, '_pccc_price', true);
        ?>
            <a class="w-full flex gap-2 mb-5" href="<?= esc_url(get_permalink($ph)) ?>">
                <div class="min-w-[120px] w-[120px] border rounded">
                    <img class="w-full max-h-full rounded" src="<?= esc_url(pccc24_post_image_url($ph)) ?>" alt="<?= esc_attr($ph->post_title) ?>" />
                </div>
                <div>
                    <div class="limit-line limit-line-3 text-base mb-2">
                        <?= esc_html($ph->post_title) ?>
                    </div>
                    <div class="text-base text-amber-600">
                        <?php if ($ph_price > 0) : ?>
                            <span><?= esc_html(pccc24_price_format($ph_price)) ?>đ</span>
                        <?php else : ?>
                            <span>Liên hệ</span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
