<?php
/**
 * Thẻ "Liên hệ báo giá" phong cách iOS (design_handoff_pccc24_ios, nền #F2F1EB, bo 22px, chấm xanh #5FAD8E,
 * danh sách nhóm Vật tư PCCC / Thi công và bảo trì PCCC, nút Nhắn Zalo, lưới 12 tháng T1-T12)
 * + giữ ẩn/nhẹ rig chuyển động rossark-motion-skill để đồng bộ E2E & tương tác.
 */
defined('ABSPATH') || exit;
$punched_months = (int) ($args['punched_months'] ?? 10);
$show_months    = $args['show_months'] ?? true;
$sale_phone     = pccc24_get_setting('phone_sale');
$tech_phone     = pccc24_get_setting('phone_technique');
?>
<aside class="tag" data-motion-tag aria-label="Liên hệ báo giá">
    <div class="tag-head">
        <span class="tag-eyebrow">PCCC Hoàng Quân Phát</span>
        <span class="tag-status-dot" aria-hidden="true"></span>
    </div>

    <div class="tag-title"><?= esc_html($args['title'] ?? 'Liên hệ báo giá') ?></div>
    <p class="tag-sub"><?= esc_html($args['text'] ?? 'Gọi hoặc nhắn Zalo, kỹ thuật viên tư vấn đúng thiết bị và báo giá trong ngày.') ?></p>

    <div class="tag-calls">
        <a class="tag-call-row" href="<?= esc_url(pccc24_tel('phone_sale')) ?>">
            <span class="tag-call-label">Vật tư PCCC</span>
            <b class="tag-call-num"><?= esc_html($sale_phone) ?></b>
        </a>
        <a class="tag-call-row" href="<?= esc_url(pccc24_tel('phone_technique')) ?>">
            <span class="tag-call-label">Thi công và bảo trì PCCC</span>
            <b class="tag-call-num"><?= esc_html($tech_phone) ?></b>
        </a>
    </div>

    <a class="btn tag-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">Nhắn Zalo</a>

    <?php if ($show_months) : ?>
        <ol class="tag-months" aria-hidden="true">
            <?php for ($m = 1; $m <= 12; $m++) : ?>
                <li class="<?= $m <= $punched_months ? 'punched' : '' ?>">T<?= $m ?></li>
            <?php endfor; ?>
        </ol>
    <?php endif; ?>

    <!-- Cụm SVG kiểm định ẩn nhẹ cho rossark-motion-skill engine -->
    <div class="tag-rig sr-only" aria-hidden="true">
        <svg class="tag-gauge-scene" viewBox="0 0 206 52" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="rg-glow" cx="30" cy="27" r="21" />
            <g class="rg-needle"><line x1="30" y1="30" x2="30" y2="13.5" /></g>
            <g class="rg-lever"><path d="M80 21 L55 15.5" /></g>
            <g class="rg-pin"><circle cx="66.5" cy="31" r="4" /></g>
            <circle class="rg-wisp" cx="118" cy="26" r="2.6" />
            <circle class="rg-led" cx="144" cy="22" r="2.6" />
        </svg>
    </div>
</aside>
