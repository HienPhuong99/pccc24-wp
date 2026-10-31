<?php
/**
 * Chặng B: Bottom Sheet báo giá kiểu iOS trên mobile (<dialog id="quote-sheet">).
 * Trên cùng là Gọi / Zalo trực tiếp; bên dưới là form ngắn để khách ngoài giờ để lại SĐT yêu cầu gọi lại.
 */
defined('ABSPATH') || exit;
?>
<dialog id="quote-sheet" class="ios-sheet" aria-label="Báo giá nhanh">
    <div class="ios-sheet-handle" data-sheet-drag aria-hidden="true">
        <span class="ios-sheet-pill"></span>
    </div>
    <button type="button" class="dialog-close" data-close aria-label="Đóng">×</button>

    <div class="ios-sheet-body">
        <h2 class="ios-sheet-title">Báo giá &amp; Tư vấn PCCC</h2>
        <p class="ios-sheet-sub">Gọi hoặc nhắn Zalo để nhận báo giá ngay lập tức:</p>

        <div class="ios-sheet-direct">
            <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_sale')) ?>">
                <?= pccc24_icon('phone') ?>
                <span><small>Bán hàng</small><?= pccc24_get_setting('phone_sale') ?></span>
            </a>
            <a class="btn btn-call" href="<?= esc_url(pccc24_tel('phone_technique')) ?>">
                <?= pccc24_icon('phone') ?>
                <span><small>Kỹ thuật</small><?= pccc24_get_setting('phone_technique') ?></span>
            </a>
            <a class="btn btn-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">
                <?= pccc24_icon('chat') ?>
                <span><small>Phản hồi nhanh</small>Nhắn Zalo</span>
            </a>
        </div>

        <form id="quote-form" class="ios-sheet-form" novalidate>
            <p class="ios-sheet-divider"><span>Hoặc để lại SĐT — Kỹ thuật viên gọi lại</span></p>

            <!-- Honeypot chống spam -->
            <div class="sr-only" aria-hidden="true">
                <label for="quote-website">Website</label>
                <input type="text" id="quote-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="ios-sheet-row">
                <label for="quote-name">Tên của bạn *</label>
                <input type="text" id="quote-name" name="name" required placeholder="Ví dụ: Anh Minh (Kho xưởng Thủ Đức)">
            </div>

            <div class="ios-sheet-row">
                <label for="quote-phone">Số điện thoại *</label>
                <input type="tel" id="quote-phone" name="phone" required inputmode="tel" placeholder="09xxxxxxxx">
            </div>

            <div class="ios-sheet-row">
                <label for="quote-need">Nhu cầu tư vấn</label>
                <select id="quote-need" name="need">
                    <option value="Bình chữa cháy">Bình chữa cháy</option>
                    <option value="Thi công hệ thống">Thi công hệ thống</option>
                    <option value="Bảo trì">Bảo trì</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>

            <div class="ios-sheet-row">
                <label for="quote-note">Ghi chú thêm (tuỳ chọn)</label>
                <input type="text" id="quote-note" name="note" placeholder="Số lượng dự kiến, tên thiết bị cần báo giá…">
            </div>

            <button type="submit" class="btn btn-call ios-sheet-submit">Gửi yêu cầu gọi lại</button>
            <p class="ios-sheet-msg" id="quote-msg" role="status" aria-live="polite" hidden></p>
        </form>
    </div>
</dialog>
