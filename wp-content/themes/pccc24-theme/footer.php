<?php
/**
 * Footer theo PCCC24 iOS Handoff:
 * Thông tin liên hệ (grouped card + bản đồ Google Maps có thanh kính mờ "Chỉ đường"),
 * 3 nhóm trang với dấu ›, thanh bản quyền + 3 nút pill mạng xã hội,
 * thanh nổi .float-contact (Gọi báo giá + Zalo), Bottom Sheet báo giá & Xem nhanh.
 */
defined('ABSPATH') || exit;
$footer_menus  = pccc24_get_footer_menus();
$service_types = pccc24_get_service_types();
?>
    </div>
</main>

<footer class="footer">
    <h2 class="footer-heading">Thông tin liên hệ</h2>

    <div class="footer-top-grid">
        <div class="footer-info-col">
            <div class="footer-company-title"><?= pccc24_get_setting('company') ?></div>
            <div class="ios-group-card">
                <div class="ios-group-row">
                    <span class="ios-row-label">Văn phòng đại diện</span>
                    <span class="ios-row-val">262 Đỗ Mười, Phường Tam Bình, Thành phố Hồ Chí Minh</span>
                </div>
                <div class="ios-group-row">
                    <span class="ios-row-label">Trụ sở chính</span>
                    <span class="ios-row-val">Số 16, Đường N4, khu nhà ở TM DV Phú Mỹ, khu 5, phường Bình Dương, Thành phố Hồ Chí Minh</span>
                </div>
                <div class="ios-group-row">
                    <span class="ios-row-label">Điện thoại</span>
                    <a class="ios-row-link" href="<?= esc_url(pccc24_tel('phone')) ?>"><?= pccc24_get_setting('phone') ?></a>
                </div>
                <div class="ios-group-row">
                    <span class="ios-row-label">Email</span>
                    <a class="ios-row-link" href="mailto:<?= esc_attr(pccc24_get_setting('email', false)) ?>"><?= pccc24_get_setting('email') ?></a>
                </div>
                <div class="ios-group-row">
                    <span class="ios-row-label">GPKD</span>
                    <span class="ios-row-val"><?= pccc24_get_setting('gpkd') ?></span>
                </div>
            </div>
        </div>

        <div class="footer-map-card">
            <iframe
                title="Bản đồ PCCC Hoàng Quân Phát"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=262%20%C4%90%E1%BB%97%20M%C6%B0%E1%BB%9Di%2C%20Tam%20B%C3%ACnh%2C%20Th%E1%BB%A7%20%C4%90%E1%BB%A9c%2C%20H%E1%BB%93%20Ch%C3%AD%20Minh&amp;output=embed"
            ></iframe>
            <div class="footer-map-bar">
                <div class="footer-map-text">
                    <b>Văn phòng đại diện</b>
                    <span>262 Đỗ Mười, P. Tam Bình, TP.HCM</span>
                </div>
                <a
                    class="btn-map-dir"
                    href="https://www.google.com/maps/dir/?api=1&amp;destination=262%20%C4%90%E1%BB%97%20M%C6%B0%E1%BB%9Di%2C%20Tam%20B%C3%ACnh%2C%20TP.HCM"
                    target="_blank"
                    rel="noopener"
                >Chỉ đường</a>
            </div>
        </div>
    </div>

    <div class="footer-links-grid">
        <?php foreach ($footer_menus as $menu) : ?>
            <div class="footer-link-col">
                <div class="footer-group-label"><?= esc_html($menu->name) ?></div>
                <ul class="ios-link-group">
                    <?php foreach ($menu->pages as $p) : ?>
                        <li>
                            <a href="<?= esc_url($p->url) ?>">
                                <span><?= esc_html($p->name) ?></span>
                                <span class="chev" aria-hidden="true">›</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer-bottom-bar">
        <span>© <?= esc_html(wp_date('Y')) ?> PCCC24 — Công ty TNHH MTV Hoàng Quân Phát.</span>
        <div class="footer-socials">
            <a class="social-pill" href="<?= esc_url(pccc24_get_setting('facebook', false)) ?>" target="_blank" rel="noopener">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                <span>Facebook</span>
            </a>
            <a class="social-pill" href="<?= esc_url(pccc24_get_setting('youtube', false)) ?>" target="_blank" rel="noopener">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#FF0000" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                <span>Youtube</span>
            </a>
            <a class="social-pill" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#0068FF" aria-hidden="true"><circle cx="12" cy="12" r="12"/><path fill="#fff" d="M7.2 7.5h9.6v1.6L10.3 15h6.5v1.7H7v-1.6l6.5-5.9H7.2V7.5z"/></svg>
                <span>Zalo</span>
            </a>
        </div>
    </div>
</footer>

<div class="float-contact" role="region" aria-label="Liên hệ nhanh">
    <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>" data-quote-open><?= pccc24_icon('phone') ?> Gọi báo giá</a>
    <a class="btn btn-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener"><?= pccc24_icon('chat') ?> Zalo</a>
</div>

<?php get_template_part('template-parts/quote-sheet'); ?>

<dialog id="quickview" class="ios-sheet" aria-label="Xem nhanh sản phẩm">
    <div class="ios-sheet-handle" aria-hidden="true"><span class="ios-sheet-pill"></span></div>
    <div class="ios-sheet-head">
        <span class="ios-sheet-head-title">Xem nhanh thiết bị</span>
        <button type="button" class="dialog-close" data-close aria-label="Đóng">✕</button>
    </div>
    <div class="quickview ios-sheet-body"></div>
</dialog>

<dialog id="drawer" class="drawer" aria-label="Menu">
    <button type="button" class="dialog-close" data-close aria-label="Đóng">✕</button>
    <nav>
        <details>
            <summary>Sản phẩm</summary>
            <a href="<?= esc_url(home_url('/san-pham')) ?>"><b>Tất cả sản phẩm</b></a>
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
