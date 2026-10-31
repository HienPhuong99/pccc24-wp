<?php
/** /contacts */
defined('ABSPATH') || exit;
get_header();
get_template_part('template-parts/crumbs', null, ['items' => [[null, 'Liên hệ']]]);
?>
<div class="contact">
    <div class="panel">
        <h1>Liên hệ Hoàng Quân Phát</h1>
        <dl>
            <dt>Công ty</dt><dd><?= pccc24_get_setting('company') ?></dd>
            <dt>Địa chỉ</dt><dd><?= wp_kses_post(pccc24_get_setting('address', false)) ?></dd>
            <dt>Bán hàng</dt><dd><a href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_get_setting('phone_sale') ?></a></dd>
            <dt>Kỹ thuật</dt><dd><a href="<?= esc_url(pccc24_tel('phone_technique')) ?>"><?= pccc24_get_setting('phone_technique') ?></a></dd>
            <dt>Email</dt><dd><a href="mailto:<?= esc_attr(pccc24_get_setting('email', false)) ?>"><?= pccc24_get_setting('email') ?></a></dd>
            <dt>GPKD</dt><dd><?= pccc24_get_setting('gpkd') ?></dd>
        </dl>
        <p style="display:flex;flex-wrap:wrap;gap:8px;margin-top:24px">
            <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_icon('phone') ?> Gọi bán hàng</a>
            <a class="btn btn-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener"><?= pccc24_icon('chat') ?> Nhắn Zalo</a>
        </p>
    </div>
    <iframe title="Bản đồ Hoàng Quân Phát" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4902.871758792556!2d106.7338033114599!3d10.871174557392548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317527bcfca46939%3A0x108ef6acd59f3328!2zQ8O0bmcgdHkgUENDQyBIb8OgbmcgUXXDom4gUGjDoXQ!5e1!3m2!1svi!2s!4v1784104195084!5m2!1svi!2s"></iframe>
</div>
<?php get_footer(); ?>
