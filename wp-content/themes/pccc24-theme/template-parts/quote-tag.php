<?php
/**
 * Thẻ "Liên hệ báo giá" phong cách iOS (design_handoff_pccc24_ios, nền #F2F1EB, bo 22px, chấm xanh #5FAD8E,
 * danh sách nhóm Vật tư PCCC / Thi công và bảo trì PCCC, nút Nhắn Zalo, lưới 12 tháng T1-T12)
 * + Cụm minh hoạ SVG sống động (.tag-rig > .tag-gauge-scene) chuẩn rossark-motion-skill.
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

    <!-- Cụm SVG kiểm định sống động chuẩn rossark-motion-skill (Arcs & pivots, Overlap, 1 one-shot + 3 idle loops) -->
    <div class="tag-rig" aria-hidden="true">
        <svg class="tag-gauge-scene" viewBox="0 0 206 50" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Vầng sáng trạng thái đạt chuẩn (pivot 30, 25) -->
            <circle class="rg-glow" cx="30" cy="25" r="20" fill="#0b7a4b" fill-opacity="0.11"/>
            <!-- Mặt đồng hồ áp suất -->
            <circle cx="30" cy="25" r="16.5" fill="#ffffff" stroke="#1d2329" stroke-width="1.6"/>
            <!-- Cung đỏ (hết áp) / Cung xanh (đạt chuẩn) / Cung vàng (quá áp) -->
            <path d="M18.5 31 A13 13 0 0 1 22 14.5" stroke="#c8201a" stroke-width="2.6" stroke-linecap="round"/>
            <path d="M23.5 13.6 A13 13 0 0 1 36.5 13.6" stroke="#0b7a4b" stroke-width="3.2" stroke-linecap="round"/>
            <path d="M38 14.5 A13 13 0 0 1 41.5 31" stroke="#d99b07" stroke-width="2.6" stroke-linecap="round"/>
            <!-- Kim đồng hồ áp suất (pivot 30, 25) -->
            <g class="rg-needle">
                <line x1="30" y1="27.5" x2="30" y2="13" stroke="#1d2329" stroke-width="2" stroke-linecap="round"/>
                <circle cx="30" cy="25" r="2.6" fill="#c8201a"/>
            </g>

            <!-- Cụm van bình & cần bóp (pivot 80, 20) + chốt an toàn (pivot 73, 22 - overlap follow-through) -->
            <rect x="74" y="21" width="12" height="11" rx="2.5" fill="#1d2329"/>
            <path d="M71 32 H89 L91 40 H69 Z" fill="#c8201a"/>
            <g class="rg-lever">
                <path d="M80 20 L57 14.5 C55 14 54 15.2 55.2 16.4 L76 22 Z" fill="#1d2329"/>
            </g>
            <g class="rg-pin">
                <circle cx="67" cy="29" r="3.6" stroke="#b8860b" stroke-width="1.5" fill="#fffdf5"/>
                <line x1="70" y1="26.5" x2="73" y2="22" stroke="#b8860b" stroke-width="1.3"/>
            </g>

            <!-- Vòi & loa phun + 3 hạt sương bảo vệ (seeded loop + bell fade) -->
            <path d="M86 25 Q98 25 105 24 L112 21.5 L112 28.5 L105 26" fill="#1d2329"/>
            <circle class="rg-wisp" cx="117" cy="25" r="2.4" fill="#0b7a4b" opacity="0.32"/>
            <circle class="rg-wisp" cx="122" cy="22.5" r="2.0" fill="#0b7a4b" opacity="0.24"/>
            <circle class="rg-wisp" cx="120" cy="27.5" r="1.7" fill="#0b7a4b" opacity="0.20"/>

            <!-- Nhãn trạng thái kiểm định (bên phải) -->
            <rect x="135" y="13" width="63" height="24" rx="7" fill="#ffffff" stroke="#1d2329" stroke-opacity="0.10"/>
            <circle class="rg-led" cx="144" cy="25" r="3" fill="#0b7a4b"/>
            <text x="151" y="23" fill="#1d2329" font-size="6.8" font-weight="700" letter-spacing="0.03em">ĐẠT CHUẨN</text>
            <text x="151" y="31" fill="#4a545e" font-size="5.8" font-weight="600">TEM KIỂM ĐỊNH</text>
        </svg>
    </div>

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
</aside>
