<?php
/**
 * Cây danh mục sản phẩm 2 cấp (menu, trang chủ, sidebar). Cấp 2 mở khi hover/focus.
 */
defined('ABSPATH') || exit;
$current = is_tax('pccc_product_cat') ? get_queried_object_id() : 0;
?>
<ul class="cat-list">
    <?php foreach (pccc24_get_category_tree() as $root) : ?>
        <li>
            <a href="<?= esc_url($root->url) ?>"<?= $current === $root->term_id ? ' aria-current="page"' : '' ?>>
                <span><?= esc_html($root->name) ?></span>
                <?= $root->childrens ? '<span class="count">' . count($root->childrens) . '</span>' : '' ?>
            </a>
            <?php if ($root->childrens) : ?>
                <ul>
                    <?php foreach ($root->childrens as $child) : ?>
                        <li><a href="<?= esc_url($child->url) ?>"<?= $current === $child->term_id ? ' aria-current="page"' : '' ?>><?= esc_html($child->name) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
