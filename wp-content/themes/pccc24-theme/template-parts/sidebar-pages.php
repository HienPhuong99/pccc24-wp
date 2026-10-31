<?php
/**
 * Sidebar cột trái cho Trang tĩnh & Nhóm trang (tương đương app/Views/pages/left.php)
 */

defined('ABSPATH') || exit;

$page_type = null;
if (is_tax('pccc_page_type')) {
    $page_type = get_queried_object();
} elseif (is_singular('page')) {
    $terms = get_the_terms(get_the_ID(), 'pccc_page_type');
    if (!is_wp_error($terms) && !empty($terms)) {
        $page_type = reset($terms);
    }
}

if (!$page_type || is_wp_error($page_type)) {
    $all_pt = get_terms(['taxonomy' => 'pccc_page_type', 'hide_empty' => false, 'number' => 1]);
    $page_type = (!is_wp_error($all_pt) && !empty($all_pt)) ? reset($all_pt) : null;
}

$sidebar_pages = [];
if ($page_type) {
    $raw_pages = get_posts([
        'post_type'   => 'page',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => 'ID',
        'order'       => 'ASC',
        'tax_query'   => [[
            'taxonomy' => 'pccc_page_type',
            'field'    => 'term_id',
            'terms'    => $page_type->term_id,
        ]],
    ]);
    foreach ($raw_pages as $sp) {
        if (str_starts_with($sp->post_name, 'tac-gia-')) {
            continue;
        }
        $custom_url = trim((string) get_post_meta($sp->ID, '_pccc_url', true));
        $sidebar_pages[] = (object) [
            'ID'   => $sp->ID,
            'name' => $sp->post_title,
            'slug' => $sp->post_name,
            'url'  => $custom_url !== '' ? $custom_url : get_permalink($sp),
        ];
    }
}
?>
<?php if ($page_type) : ?>
<div class="border-tab"></div>
<div class="flex justify-between items-center">
    <div class="w-full">
        <label class="popular-label popular-label-icon w-full">
            <i class="bx bx-cog"></i>
            <span class="text">
                <?php echo esc_html($page_type->name); ?>
            </span>
        </label>
    </div>
</div>
<div class="mb-5 border p-2">
    <?php foreach ($sidebar_pages as $sp) : ?>
        <a class="w-full flex gap-2 my-3" href="<?php echo esc_url($sp->url); ?>">
            <span class="limit-line limit-line-4 text-base">
                <?php echo esc_html($sp->name); ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
