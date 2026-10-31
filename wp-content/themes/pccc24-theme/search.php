<?php
/**
 * Product Search Template (/san-pham/tim-kiem?q=..., replicated 100% from app/Views/products/search.php)
 */
defined('ABSPATH') || exit;

get_header();

$query_str = sanitize_text_field(wp_unslash($_GET['q'] ?? get_search_query()));
?>

<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar-categories'); ?>
    </div>
    <div class="right-content w-full">
        <div class="mt-1 flex items-center justify-between">
            <h1 class="text-2xl">Tìm kiếm sản phẩm: <?= esc_html($query_str) ?></h1>
        </div>

        <div class="content-body mt-2">
            <?php if (have_posts()) : ?>
                <div class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-3 xs-s:grid-cols-2 gap-5 mt-5">
                    <?php while (have_posts()) : the_post();
                        get_template_part('template-parts/product-card', null, ['product' => get_post()]);
                    endwhile; ?>
                </div>
                <div class="paginate-container mt-5">
                    <?php pccc24_render_pagination(); ?>
                </div>
            <?php else : ?>
                <p class="mt-5 text-gray-500">Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?= esc_html($query_str) ?>".</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
