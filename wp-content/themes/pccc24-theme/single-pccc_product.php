<?php
/**
 * Template chi tiết Sản phẩm (/san-pham/{slug}/{id})
 * Tương đương app/Views/products/show.php
 */

defined('ABSPATH') || exit;

get_header();

the_post();
$post_id      = get_the_ID();
$old_id       = (int) get_post_meta($post_id, '_pccc_old_id', true) ?: $post_id;
$product_name = get_the_title();
$price        = (float) get_post_meta($post_id, '_pccc_price', true);
$img_url      = pccc24_post_image_url($post_id);
$permalink    = get_permalink($post_id);

$specs = get_post_meta($post_id, '_pccc_specs', true);
if (!is_array($specs)) {
    $specs = [];
}

$policy_ids = get_post_meta($post_id, '_pccc_policy_ids', true);
$product_policies = [];
if (is_array($policy_ids) && !empty($policy_ids)) {
    $product_policies = get_posts([
        'post_type'   => 'pccc_policy',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'meta_query'  => [[
            'key'     => '_pccc_old_id',
            'value'   => array_map('intval', $policy_ids),
            'compare' => 'IN',
        ]],
    ]);
}

$terms    = get_the_terms($post_id, 'pccc_product_cat');
$category = (!is_wp_error($terms) && !empty($terms)) ? reset($terms) : null;
$parent_cat = ($category && $category->parent) ? get_term($category->parent, 'pccc_product_cat') : null;
?>
<style>
    /* Ép tất cả các bảng trong khu vực nội dung thành màu xám nhạt */
    .content-body table, 
    .content-body table * {
        background-color: #f8fafc !important; 
    }
</style>
<div class="flex gap-10 mt-4">
    <div class="left-content md:block xs-s:hidden">
        <?php get_template_part('template-parts/sidebar', 'categories'); ?>
    </div>
    <div class="right-content w-full">
        <div class="crumb-box flex h-10 items-center">
            <ul class="crumb">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        Trang chủ
                    </a>
                </li>
                <?php if ($parent_cat && !is_wp_error($parent_cat)) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($parent_cat)); ?>">
                            <?php echo esc_html($parent_cat->name); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($category) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($category)); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="grid md:grid-cols-4 xs-s:grid-cols-1 mt-4">
            <div class="col-span-2">
                <div class="md:pr-5 xs-s:pr-0">
                    <div class="max-h-[360px] flex items-center justify-center bg-white relative rounded shadow">
                        <img data-id="lightbox-<?php echo esc_attr($old_id); ?>"
                             src="<?php echo esc_url($img_url); ?>"
                             alt="<?php echo esc_attr($product_name); ?>"
                             class="img-product-responsive max-h-[360px] lightbox cursor-pointer"
                             data-href="<?php echo esc_url($img_url); ?>">
                    </div>
                </div>
            </div>
            <div class="col-span-2 md:mt-0 xs-s:mt-5">
                <h1 class="text-2xl"><?php echo esc_html($product_name); ?></h1>
                <div class="my-3">
                    <span class="text-2xl text-red-600">
                        <?php if ($price > 0) : ?>
                            <span><?php echo esc_html(pccc24_price_format($price)); ?>đ</span>
                        <?php else : ?>
                            <span>Liên hệ</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="my-3">
                    <?php if (!empty($specs)) : ?>
                        <table class="mb-3 product-property-table">
                            <?php foreach ($specs as $property) :
                                $prop_name    = is_array($property) ? ($property['name'] ?? '') : ($property->name ?? '');
                                $prop_content = is_array($property) ? ($property['content'] ?? '') : ($property->content ?? '');
                                ?>
                                <tr>
                                    <td><?php echo esc_html($prop_name); ?></td>
                                    <td><?php echo esc_html($prop_content); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>

                    <div class="flex items-center gap-1">
                        <span class="text-base text-gray-600">Hỗ trợ bán hàng</span>
                        <span class="lg:text-xl md:text-lg xs-s:text-base text-red-pccc">
                            <?php echo pccc24_get_setting('phone_sale'); ?>
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-base text-gray-600">Hỗ trợ kỹ thuật</span>
                        <span class="lg:text-xl md:text-lg xs-s:text-base text-red-pccc">
                            <?php echo pccc24_get_setting('phone_technique'); ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($product_policies)) : ?>
                    <div class="product-policy my-5 p-4 rounded bg-white-100">
                        <?php foreach ($product_policies as $product_policy) : ?>
                            <div class="flex items-center gap-2 my-2">
                                <img src="<?php echo esc_url(pccc24_post_image_url($product_policy)); ?>" class="w-8 h-8" alt="<?php echo esc_attr($product_policy->post_title); ?>" />
                                <?php echo wp_kses_post($product_policy->post_content ?: $product_policy->post_title); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-5 flex gap-4 items-center social">
                    <span>Chia sẻ:</span>
                    <a class="mr-3"
                       href="http://www.facebook.com/sharer.php?u=<?php echo rawurlencode($permalink); ?>"
                       target="_blank" title="Share Facebook" rel="nofollow">
                        <svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="#1877f2" height="512" rx="15%" width="512" />
                            <path d="m355.6 330 11.4-74h-71v-48c0-20.2 9.9-40 41.7-40h32.3v-63s-29.3-5-57.3-5c-58.5 0-96.7 35.4-96.7 99.6v56.4h-65v74h65v182h80v-182z" fill="#fff" />
                        </svg>
                    </a>

                    <a class="mr-3"
                       href="https://twitter.com/share?url=<?php echo rawurlencode($permalink); ?>"
                       target="_blank" title="Share Twitter" rel="nofollow">
                        <svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="#1da1f2" height="512" rx="15%" width="512" />
                            <path d="m437 152a72 72 0 0 1 -40 12 72 72 0 0 0 32-40 72 72 0 0 1 -45 17 72 72 0 0 0 -122 65 200 200 0 0 1 -145-74 72 72 0 0 0 22 94 72 72 0 0 1 -32-7 72 72 0 0 0 56 69 72 72 0 0 1 -32 1 72 72 0 0 0 67 50 200 200 0 0 1 -105 29 200 200 0 0 0 309-179 200 200 0 0 0 35-37" fill="#fff" />
                        </svg>
                    </a>

                    <a class="mr-3"
                       href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode($permalink); ?>"
                       target="_blank" title="Share Linkedin" rel="nofollow">
                        <svg viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                            <path d="m116 3h-104a8.91 8.91 0 0 0 -9 8.8v104.42a8.91 8.91 0 0 0 9 8.78h104a8.93 8.93 0 0 0 9-8.81v-104.42a8.93 8.93 0 0 0 -9-8.77z" fill="#0076b2" />
                            <g fill="#fff">
                                <path d="m21.06 48.73h18.11v58.27h-18.11zm9.06-29a10.5 10.5 0 1 1 -10.5 10.49 10.5 10.5 0 0 1 10.5-10.49" />
                                <path d="m50.53 48.73h17.36v8h.24c2.42-4.58 8.32-9.41 17.13-9.41 18.34-.04 21.74 12.03 21.74 27.68v32h-18.11v-28.35c0-6.75-.12-15.44-9.41-15.44s-10.87 7.36-10.87 15v28.79h-18.08z" />
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-5 border-t pt-4">
            <h2 class="text-2xl">Thông tin sản phẩm</h2>
            <div class="content-body">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
