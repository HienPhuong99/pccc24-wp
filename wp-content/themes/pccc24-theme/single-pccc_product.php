<?php
/**
 * Chi tiết sản phẩm (/san-pham/{slug}/{id}).
 */
defined('ABSPATH') || exit;
get_header();
the_post();

$id     = get_the_ID();
$price  = (int) get_post_meta($id, '_pccc_price', true);
$specs  = (array) get_post_meta($id, '_pccc_specs', true);
$pids   = array_map('intval', (array) get_post_meta($id, '_pccc_policy_ids', true));
$policies = $pids ? get_posts([
    'post_type' => 'pccc_policy', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC',
    'meta_query' => [['key' => '_pccc_old_id', 'value' => $pids, 'compare' => 'IN']],
]) : [];
$youtube = (string) get_post_meta($id, '_pccc_youtube_video', true);
$catalog = (string) get_post_meta($id, '_pccc_catalog_url', true);

$crumbs = [[home_url('/san-pham'), 'Sản phẩm']];
$terms  = get_the_terms($id, 'pccc_product_cat');
if ($terms && !is_wp_error($terms)) {
    $cat = $terms[0];
    if ($cat->parent && ($parent = get_term($cat->parent, 'pccc_product_cat')) && !is_wp_error($parent)) {
        $crumbs[] = [get_term_link($parent), $parent->name];
    }
    $crumbs[] = [get_term_link($cat), $cat->name];
}
$share = rawurlencode(get_permalink());
?>

<?php get_template_part('template-parts/crumbs', null, ['items' => $crumbs]); ?>

<article>
    <div class="product-detail">
        <div class="main-img"><img src="<?= esc_url(pccc24_post_image_url($id)) ?>" alt="<?= esc_attr(get_the_title()) ?>"></div>
        <div>
            <h1><?php the_title(); ?></h1>
            <p class="product-price"><?= $price > 0 ? esc_html(pccc24_price_format($price)) . 'đ' : 'Giá: liên hệ để nhận báo giá theo số lượng' ?></p>
            <?php if ($specs) : ?>
                <table class="specs">
                    <?php foreach ($specs as $s) : ?>
                        <tr><th><?= esc_html($s['name'] ?? '') ?></th><td><?= esc_html($s['content'] ?? '') ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <?php if ($policies) : ?>
                <ul class="policies">
                    <?php foreach ($policies as $p) : ?>
                        <li><img src="<?= esc_url(pccc24_post_image_url($p)) ?>" alt=""><?= esc_html($p->post_title) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($catalog) : ?>
                <p><a class="btn btn-line" href="<?= esc_url($catalog) ?>" target="_blank" rel="noopener">Tải catalog</a></p>
            <?php endif; ?>
            <div class="share">
                Chia sẻ
                <a href="https://www.facebook.com/sharer.php?u=<?= $share ?>" target="_blank" rel="nofollow noopener">Facebook</a>
                <a href="https://zalo.me/share?url=<?= $share ?>" target="_blank" rel="nofollow noopener">Zalo</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $share ?>" target="_blank" rel="nofollow noopener">LinkedIn</a>
            </div>
        </div>
        <?php get_template_part('template-parts/quote-tag', null, [
            'title' => 'Báo giá sản phẩm này',
            'text'  => 'Báo mã “' . get_the_title() . '” khi gọi để được giá sỉ, thời gian giao và chứng từ kiểm định.',
        ]); ?>
    </div>

    <div class="product-body panel">
        <h2>Thông tin sản phẩm</h2>
        <?php if ($youtube && preg_match('~(?:v=|youtu\.be/|embed/)([\w-]{11})~', $youtube, $m)) : ?>
            <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/<?= esc_attr($m[1]) ?>" title="Video sản phẩm" loading="lazy" allowfullscreen style="max-width:100%;border:0"></iframe>
        <?php endif; ?>
        <div class="prose"><?php the_content(); ?></div>
    </div>
</article>

<?php get_footer(); ?>
