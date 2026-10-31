<?php
/**
 * Product Category Taxonomy Template (replicated 100% from app/Views/categories/show.php)
 */
defined('ABSPATH') || exit;

get_header();

$category    = get_queried_object();
$sorted      = sanitize_text_field(wp_unslash($_GET['sorted'] ?? ''));
$cat_content = (string) get_term_meta($category->term_id, '_pccc_content', true);
$filters     = get_term_meta($category->term_id, '_pccc_filters', true);
?>

<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar-categories'); ?>
    </div>
    <div class="right-content w-full">
        <div class="mt-1 flex items-center justify-between">
            <h1 class="text-2xl"><?= esc_html($category->name) ?></h1>

            <div class="flex gap-3">
                <div class="SortSearch" data-props='<?= esc_attr(wp_json_encode(['sorted' => $sorted])) ?>'></div>
            </div>
        </div>

        <?php if (!empty($filters) && is_array($filters)) :
            $filter_props = array_map(function ($g) {
                return [
                    'id'        => $g['id'] ?? 0,
                    'name'      => $g['name'] ?? '',
                    'childrens' => $g['values'] ?? [],
                ];
            }, $filters);
        ?>
            <div class="mt-3 p-3 bg-white rounded border" id="filterProduct" data-props='<?= esc_attr(wp_json_encode(['category_properties' => $filter_props], JSON_UNESCAPED_UNICODE)) ?>'></div>
        <?php endif; ?>

        <div class="content-body mt-2">
            <div class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-3 xs-s:grid-cols-2 gap-5 mt-5">
                <?php while (have_posts()) : the_post();
                    get_template_part('template-parts/product-card', null, ['product' => get_post()]);
                endwhile; ?>
            </div>

            <div class="paginate-container mt-8 mb-6">
                <?php pccc24_render_pagination(); ?>
            </div>

            <?php if ($cat_content !== '') : ?>
                <div class="mt-4 mb-5 bg-slate-50 text-justify">
                    <?= wp_kses_post($cat_content) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
