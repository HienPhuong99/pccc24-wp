<?php
/**
 * Footer: thông tin công ty, nhóm trang, menu mobile, popup xem nhanh, nút gọi/Zalo.
 */
defined('ABSPATH') || exit;
$footer_menus  = pccc24_get_footer_menus();
$service_types = pccc24_get_service_types();
?>
    </div>
</main>

<footer class="footer">
    <div class="wrap">
        <div class="footer-cols">
            <div class="footer-company">
                <b><?= pccc24_get_setting('company') ?></b>
                <p><?= wp_kses_post(pccc24_get_setting('address', false)) ?></p>
                <p>Điện thoại <a href="<?= esc_url(pccc24_tel('phone')) ?>"><?= pccc24_get_setting('phone') ?></a> · Email <a href="mailto:<?= esc_attr(pccc24_get_setting('email', false)) ?>"><?= pccc24_get_setting('email') ?></a></p>
                <p>GPKD <?= pccc24_get_setting('gpkd') ?></p>
                <p>
                    <a href="<?= esc_url(pccc24_get_setting('facebook', false)) ?>" target="_blank" rel="noopener">Facebook</a> ·
                    <a href="<?= esc_url(pccc24_get_setting('youtube', false)) ?>" target="_blank" rel="noopener">Youtube</a> ·
                    <a href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">Zalo</a>
                </p>
            </div>
            <?php foreach ($footer_menus as $menu) : ?>
                <div>
                    <h2><?= esc_html($menu->name) ?></h2>
                    <ul>
                        <?php foreach ($menu->pages as $p) : ?>
                            <li><a href="<?= esc_url($p->url) ?>"><?= esc_html($p->name) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="footer-bottom">© <?= esc_html(wp_date('Y')) ?> PCCC24 — Thiết bị phòng cháy chữa cháy Hoàng Quân Phát.</p>
    </div>
</footer>

<div class="float-contact">
    <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_icon('phone') ?> Gọi báo giá</a>
    <a class="btn btn-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener"><?= pccc24_icon('chat') ?> Zalo</a>
</div>

<dialog id="quickview" aria-label="Xem nhanh sản phẩm">
    <button type="button" class="dialog-close" data-close aria-label="Đóng">×</button>
    <div class="quickview"></div>
</dialog>

<dialog id="drawer" class="drawer" aria-label="Menu">
    <button type="button" class="dialog-close" data-close aria-label="Đóng">×</button>
    <nav>
        <details>
            <summary>Sản phẩm</summary>
            <?php foreach (pccc24_get_category_tree() as $root) : ?>
                <a href="<?= esc_url($root->url) ?>"><?= esc_html($root->name) ?></a>
            <?php endforeach; ?>
        </details>
        <?php foreach ($service_types as $st) : ?>
            <a href="<?= esc_url($st->url) ?>"><?= esc_html($st->name) ?></a>
        <?php endforeach; ?>
        <a href="<?= esc_url(home_url('/tin-tuc')) ?>">Tin tức</a>
        <a href="<?= esc_url(home_url('/pages/gioi-thieu')) ?>">Về chúng tôi</a>
        <a href="<?= esc_url(home_url('/contacts')) ?>">Liên hệ</a>
    </nav>
    <div class="contact-mini">
        <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_icon('phone') ?> Bán hàng <?= pccc24_get_setting('phone_sale') ?></a>
        <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_technique')) ?>"><?= pccc24_icon('phone') ?> Kỹ thuật <?= pccc24_get_setting('phone_technique') ?></a>
    </div>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
