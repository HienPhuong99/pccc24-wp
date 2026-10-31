<?php
/**
 * Template chi tiết Trang tĩnh (/pages/{slug})
 * Tương đương app/Views/pages/show.php
 */

defined('ABSPATH') || exit;

get_header();

the_post();
$post_id = get_the_ID();
$terms = get_the_terms($post_id, 'pccc_page_type');
$page_type = (!is_wp_error($terms) && !empty($terms)) ? reset($terms) : null;
?>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'pages'); ?>
    </div>
    <div class="right-content w-full">
        <div class="crumb-box sm:flex xs-s:grid gap-4 justify-between items-center">
            <ul class="crumb">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        Trang chủ
                    </a>
                </li>
                <?php if ($page_type) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($page_type)); ?>">
                            <?php echo esc_html($page_type->name); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <style>
        /* Ép chết MỌI THÀNH PHẦN trong bảng về màu nền #f8fafc */
        .content-body figure.table,
        .content-body table,
        .content-body table tbody,
        .content-body table tr,
        .content-body table td,
        .content-body table th,
        .content-body table p,
        .content-body table span,
        .content-body table strong,
        .content-body table b {
            background-color: #f8fafc !important;
            background: #f8fafc !important;
        }
        </style>

        <div class="content-body mt-2">
            <h1 class="text-2xl mb-4 font-medium"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </div>
    </div>
</div>
<?php
get_footer();
