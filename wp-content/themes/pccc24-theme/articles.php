<?php
/**
 * Template danh sách Tin tức (/tin-tuc)
 * Tương đương app/Views/articles/index.php
 */

defined('ABSPATH') || exit;

get_header();
?>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'articles'); ?>
    </div>
    <div class="right-content w-full">
        <div class="crumb-box flex h-10 justify-between items-center">
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
        </div>

        <div class="content-body mt-2">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post();
                    $aid  = get_the_ID();
                    $aurl = get_permalink($aid);
                    $desc = (string) get_post_meta($aid, '_pccc_description', true);
                    if ($desc === '') {
                        $desc = get_the_excerpt($aid);
                    }
                    ?>
                    <div class="sm:flex xs-s:grid gap-5 mt-4 sm:mb-10 xs-s:mb-5 xs-s:border-b sm:border-none xs-s:pb-5 sm:pb-0">
                        <a href="<?php echo esc_url($aurl); ?>" class="sm:w-40 xs-s:w-full min-w-[10rem]">
                            <?php if (has_post_thumbnail($aid)) : ?>
                                <img src="<?php echo esc_url(pccc24_post_image_url($aid)); ?>"
                                     width="600" height="400"
                                     alt="<?php echo esc_attr(get_the_title($aid)); ?>"
                                     class="h-full w-full rounded object-cover">
                            <?php else : ?>
                                <div class="w-full h-24 rounded bg-slate-100 md:block xs-s:hidden"></div>
                            <?php endif; ?>
                        </a>
                        <div>
                            <a href="<?php echo esc_url($aurl); ?>">
                                <h2 class="text-base font-medium mb-3"><?php the_title(); ?></h2>
                            </a>
                            <div class="text-sm text-gray-500">
                                <?php echo wp_kses_post($desc); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
            <div class="paginate-container mt-5">
                <?php pccc24_render_pagination(); ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
