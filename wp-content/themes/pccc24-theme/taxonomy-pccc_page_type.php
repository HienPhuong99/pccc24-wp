<?php
/**
 * Template danh mục Trang tĩnh (/pages/type/{slug} và /{page-type-slug})
 * Tương đương app/Views/pages/category.php
 */

defined('ABSPATH') || exit;

get_header();

$page_type = get_queried_object();
?>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'pages'); ?>
    </div>
    <div class="right-content w-full">
        <div class="crumb-box flex h-10 justify-between items-center">
            <ul class="crumb">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        Trang chủ
                    </a>
                </li>
                <?php if ($page_type && !empty($page_type->name)) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($page_type)); ?>">
                            <?php echo esc_html($page_type->name); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="content-body mt-2">
            <div class="grid gap-4">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post();
                        $pid = get_the_ID();
                        $p_slug = get_post_field('post_name', $pid);
                        if (str_starts_with($p_slug, 'tac-gia-')) {
                            continue;
                        }
                        $custom_url = trim((string) get_post_meta($pid, '_pccc_url', true));
                        $purl = $custom_url !== '' ? $custom_url : get_permalink($pid);
                        $desc = (string) get_post_meta($pid, '_pccc_description', true);
                        if ($desc === '') {
                            $desc = get_the_excerpt($pid);
                        }
                        ?>
                        <div>
                            <a href="<?php echo esc_url($purl); ?>">
                                <h2 class="text-base font-medium mb-3"><?php the_title(); ?></h2>
                            </a>
                            <div class="text-sm text-gray-500">
                                <?php echo wp_kses_post($desc); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <div class="paginate-container mt-5">
                <?php pccc24_render_pagination(); ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
