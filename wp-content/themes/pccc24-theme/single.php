<?php
/**
 * Template chi tiết Tin tức (/tin-tuc/{slug}/{id})
 * Tương đương app/Views/articles/show.php
 */

defined('ABSPATH') || exit;

get_header();

the_post();
$post_id = get_the_ID();
?>
<style>
    /* Ép tất cả các bảng trong bài viết Tin tức thành màu xám nhạt */
    .content-body table, 
    .content-body table * {
        background-color: #f8fafc !important; 
    }
</style>

<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'articles'); ?>
    </div>
    <div class="right-content w-full">
        <div class="crumb-box sm:flex xs-s:grid gap-4 justify-between items-center">
            <ul class="crumb">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        Trang chủ
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(home_url('/tin-tuc')); ?>">
                        Tin tức
                    </a>
                </li>
            </ul>
            <span class="text-sm text-gray-500"><?php echo esc_html(pccc24_time_string($post_id)); ?></span>
        </div>

        <div class="content-body mt-2">
            <h1 class="text-2xl mb-4 font-medium"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </div>
    </div>
</div>
<?php
get_footer();
