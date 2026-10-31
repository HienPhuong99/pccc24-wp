<?php
/**
 * Sidebar Popular Articles (replicated from app/Views/articles/left.php)
 */
defined('ABSPATH') || exit;

$popular_articles = pccc24_get_popular_articles(5);
?>
<div class="md:block xs-s:hidden">
    <div class="border-tab"></div>
    <div class="flex justify-between items-center mb-2">
        <div class="w-full">
            <label class="popular-label popular-label-icon w-full">
                <i class="bx bxs-hot"></i>
                <span class="text">Tin nổi bật</span>
            </label>
        </div>
    </div>
    <div>
        <?php foreach ($popular_articles as $pa) : ?>
            <a class="block w-full bg-white shadow-sm overflow-hidden mb-5 border hover:opacity-90 transition-opacity" style="border-radius: 6px;" href="<?= esc_url(get_permalink($pa)) ?>">
                <div class="w-full h-44 overflow-hidden bg-slate-100" style="border-top-left-radius: 6px; border-top-right-radius: 6px;">
                    <img class="w-full h-full object-cover" style="border-top-left-radius: 6px; border-top-right-radius: 6px;" src="<?= esc_url(pccc24_post_image_url($pa)) ?>" width="600" height="400" alt="<?= esc_attr($pa->post_title) ?>" />
                </div>
                <div class="p-3 pt-1 text-center">
                    <span class="limit-line limit-line-3 text-base font-semibold block" style="color: #000000;">
                        <?= esc_html($pa->post_title) ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
