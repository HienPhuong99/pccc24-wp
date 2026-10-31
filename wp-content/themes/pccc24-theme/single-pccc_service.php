<?php
/**
 * Template chi tiết Dịch vụ (/dich-vu/{slug}/{id})
 * Tương đương app/Views/services/show.php
 */

defined('ABSPATH') || exit;

get_header();

the_post();
$post_id = get_the_ID();
$terms   = get_the_terms($post_id, 'pccc_service_type');
$service_type = (!is_wp_error($terms) && !empty($terms)) ? reset($terms) : null;
?>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'services'); ?>
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
                    <a href="<?php echo esc_url(home_url('/dich-vu')); ?>">
                        Dịch vụ
                    </a>
                </li>
                <?php if ($service_type) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($service_type)); ?>">
                            <?php echo esc_html($service_type->name); ?>
                        </a>
                    </li>
                <?php endif; ?>
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
