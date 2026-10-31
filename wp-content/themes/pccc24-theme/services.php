<?php
/**
 * Template danh sách Dịch vụ (/dich-vu và /dich-vu/{type-slug})
 * Tương đương app/Views/services/index.php
 */

defined('ABSPATH') || exit;

get_header();

$service_type = is_tax('pccc_service_type') ? get_queried_object() : null;
?>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'services'); ?>
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
                    <a href="<?php echo esc_url(home_url('/dich-vu')); ?>">
                        Dịch vụ
                    </a>
                </li>
                <?php if ($service_type && !empty($service_type->name)) : ?>
                    <li>
                        <?php echo esc_html($service_type->name); ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="content-body mt-2">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post();
                    $sid  = get_the_ID();
                    $surl = get_permalink($sid);
                    $desc = (string) get_post_meta($sid, '_pccc_description', true);
                    if ($desc === '') {
                        $desc = get_the_excerpt($sid);
                    }
                    ?>
                    <div class="sm:flex xs-s:grid gap-5 mt-4 sm:mb-10 xs-s:mb-5 xs-s:border-b sm:border-none xs-s:pb-5 sm:pb-0">
                        <a href="<?php echo esc_url($surl); ?>" class="sm:w-40 xs-s:w-full min-w-[10rem]">
                            <?php if (has_post_thumbnail($sid)) : ?>
                                <img src="<?php echo esc_url(pccc24_post_image_url($sid)); ?>"
                                     alt="<?php echo esc_attr(get_the_title($sid)); ?>"
                                     class="h-full w-full rounded object-cover">
                            <?php else : ?>
                                <div class="w-full h-24 rounded bg-slate-100 md:block xs-s:hidden"></div>
                            <?php endif; ?>
                        </a>
                        <div>
                            <a href="<?php echo esc_url($surl); ?>">
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
