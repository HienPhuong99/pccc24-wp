<?php
/**
 * Breadcrumb. $args['items'] = [[url|null, name], ...] (không gồm "Trang chủ").
 */
defined('ABSPATH') || exit;
?>
<ol class="crumbs" aria-label="Đường dẫn">
    <li><a href="<?= esc_url(home_url('/')) ?>">Trang chủ</a></li>
    <?php foreach ($args['items'] ?? [] as [$url, $name]) : ?>
        <li><?= $url ? '<a href="' . esc_url($url) . '">' . esc_html($name) . '</a>' : esc_html($name) ?></li>
    <?php endforeach; ?>
</ol>
