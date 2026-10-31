<?php
/**
 * Khối "Cần báo giá?" dạng thẻ kiểm định treo trên bình chữa cháy.
 * Tích hợp minh hoạ chuyển động sống (rossark-motion-skill): đồng hồ áp suất + chốt an toàn + làn sương PCCC.
 * $args['title'], $args['text'] tuỳ chọn. Dòng tháng: tháng hiện tại được "bấm lỗ" như thẻ thật.
 */
defined('ABSPATH') || exit;
$month = (int) wp_date('n');
?>
<aside class="tag tag-swing" data-motion-tag aria-label="Liên hệ báo giá">
    <div class="tag-rig" aria-hidden="true">
        <svg class="tag-gauge-scene" viewBox="0 0 206 52" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Halo trạng thái đạt chuẩn (idle glow) -->
            <circle class="rg-glow" cx="30" cy="27" r="21" fill="#0b7a4b" fill-opacity="0.16" />

            <!-- Mặt đồng hồ áp suất bình chữa cháy -->
            <circle cx="30" cy="27" r="18.5" fill="#1d2329" stroke="#1d2329" stroke-width="2" />
            <circle cx="30" cy="27" r="15.5" stroke="#eef0ee" stroke-opacity="0.22" stroke-width="1" />
            <!-- Vùng cảnh báo đỏ (áp thấp / quá áp) -->
            <path d="M17.5 34.5 A 14.5 14.5 0 0 1 21.5 15.2" stroke="#c8201a" stroke-width="3.2" stroke-linecap="round" />
            <path d="M38.5 15.2 A 14.5 14.5 0 0 1 42.5 34.5" stroke="#c8201a" stroke-width="3.2" stroke-linecap="round" />
            <!-- Vùng xanh ĐẠT CHUẨN ở đỉnh (12h) -->
            <path d="M22.5 14.6 A 14.5 14.5 0 0 1 37.5 14.6" stroke="#19b36b" stroke-width="3.8" stroke-linecap="round" />
            <!-- Vạch chia áp suất -->
            <line x1="30" y1="10.5" x2="30" y2="13" stroke="#eef0ee" stroke-width="1.2" />
            <line x1="20" y1="15.5" x2="21.5" y2="17.5" stroke="#eef0ee" stroke-width="1" />
            <line x1="40" y1="15.5" x2="38.5" y2="17.5" stroke="#eef0ee" stroke-width="1" />
            <text x="30" y="38.5" text-anchor="middle" fill="#c9d0d6" font-family="Barlow Condensed, sans-serif" font-size="6.5" font-weight="700" letter-spacing="0.5">BAR</text>

            <!-- Kim đồng hồ áp suất (pivot tại tâm 30, 27) -->
            <g class="rg-needle">
                <line x1="30" y1="30" x2="30" y2="13.5" stroke="#f3c623" stroke-width="2" stroke-linecap="round" />
                <polygon points="30,11.5 28.2,15.5 31.8,15.5" fill="#f3c623" />
                <circle cx="30" cy="27" r="3" fill="#f3c623" stroke="#1d2329" stroke-width="1.2" />
            </g>

            <!-- Cụm cổ van + vòi phun bình chữa cháy -->
            <rect x="66" y="34" width="22" height="10" rx="2" fill="#c8201a" stroke="#1d2329" stroke-width="1.5" />
            <rect x="70" y="21" width="14" height="14" rx="1.5" fill="#d8ac0f" stroke="#1d2329" stroke-width="1.5" />
            <!-- Tay cầm dưới cố định -->
            <path d="M70 23 L54 25.5" stroke="#1d2329" stroke-width="3.2" stroke-linecap="round" />
            <!-- Cần bóp van trên (pivot tại khớp 80, 21) -->
            <g class="rg-lever">
                <path d="M80 21 L55 15.5" stroke="#1d2329" stroke-width="3.2" stroke-linecap="round" />
                <circle cx="80" cy="21" r="2" fill="#f3c623" />
            </g>
            <!-- Chốt kẹp chì an toàn (pivot tại 73, 23, follow-through) -->
            <g class="rg-pin">
                <line x1="73" y1="23" x2="68" y2="28" stroke="#1d2329" stroke-width="1.4" />
                <circle cx="66.5" cy="31" r="4" fill="none" stroke="#1d2329" stroke-width="1.6" />
            </g>
            <!-- Loa phun PCCC -->
            <path d="M84 25 L98 25 L112 19.5 L112 32.5 L98 28 L84 28 Z" fill="#1d2329" />

            <!-- 3 hạt sương bảo vệ (seeded loops + bell opacity) -->
            <circle class="rg-wisp" cx="118" cy="26" r="2.6" fill="#0b7a4b" opacity="0" />
            <circle class="rg-wisp" cx="122" cy="23" r="2.1" fill="#0b7a4b" opacity="0" />
            <circle class="rg-wisp" cx="120" cy="29" r="1.8" fill="#1d2329" opacity="0" />

            <!-- Nhãn chứng nhận kiểm định -->
            <rect x="136" y="13" width="64" height="28" rx="3" fill="#1d2329" />
            <circle class="rg-led" cx="144" cy="22" r="2.6" fill="#19b36b" />
            <text x="150" y="24.5" fill="#f3c623" font-family="Barlow Condensed, sans-serif" font-size="8.5" font-weight="700" letter-spacing="0.4">KIỂM ĐỊNH</text>
            <text x="142" y="35" fill="#eef0ee" font-family="Be Vietnam Pro, sans-serif" font-size="6.5" font-weight="600">ĐẠT CHUẨN</text>
        </svg>
    </div>
    <p class="tag-title"><?= esc_html($args['title'] ?? 'Cần báo giá?') ?></p>
    <p><?= esc_html($args['text'] ?? 'Gọi hoặc nhắn Zalo, kỹ thuật viên tư vấn đúng thiết bị và báo giá trong ngày.') ?></p>
    <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>"><?= pccc24_icon('phone') ?> <span><small>Bán hàng</small><?= pccc24_get_setting('phone_sale') ?></span></a>
    <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_technique')) ?>"><?= pccc24_icon('phone') ?> <span><small>Kỹ thuật</small><?= pccc24_get_setting('phone_technique') ?></span></a>
    <a class="btn btn-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener"><?= pccc24_icon('chat') ?> Nhắn Zalo</a>
    <ol class="tag-months" aria-hidden="true">
        <?php for ($m = 1; $m <= 12; $m++) : ?>
            <li<?= $m === $month ? ' class="punched"' : '' ?>>T<?= $m ?></li>
        <?php endfor; ?>
    </ol>
</aside>
