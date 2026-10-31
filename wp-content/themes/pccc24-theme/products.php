<?php
/**
 * All Products Archive Template (/san-pham, pccc_view=products)
 */
defined('ABSPATH') || exit;

get_header();

$sorted = sanitize_text_field(wp_unslash($_GET['sorted'] ?? ''));
?>

<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar-categories'); ?>
    </div>
    <div class="right-content w-full">
        <div class="mt-1 flex items-center justify-between">
            <h1 class="text-2xl">Sản phẩm phòng cháy chữa cháy</h1>

            <div class="flex gap-3">
                <div class="SortSearch" data-props='<?= esc_attr(wp_json_encode(['sorted' => $sorted])) ?>'></div>
            </div>
        </div>

        <div class="content-body mt-2">
            <div class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-3 xs-s:grid-cols-2 gap-5 mt-5">
                <?php while (have_posts()) : the_post();
                    get_template_part('template-parts/product-card', null, ['product' => get_post()]);
                endwhile; ?>
            </div>

            <div class="paginate-container mt-8 mb-6">
                <?php pccc24_render_pagination(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
