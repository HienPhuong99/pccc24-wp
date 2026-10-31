<?php
/**
 * Bottom Sheet báo giá & tư vấn theo PCCC24 iOS Handoff (Section 11).
 */
defined('ABSPATH') || exit;
?>
<dialog id="quote-sheet" class="ios-sheet" aria-label="Báo giá nhanh">
    <div class="ios-sheet-handle" data-sheet-drag aria-hidden="true">
        <span class="ios-sheet-pill"></span>
    </div>

    <div class="ios-sheet-head">
        <h2 class="ios-sheet-title">Báo giá &amp; Tư vấn PCCC</h2>
        <button type="button" class="dialog-close" data-close aria-label="Đóng">✕</button>
    </div>

    <div class="ios-sheet-body">
        <div class="ios-sheet-group">
            <a class="ios-sheet-call-row" href="<?= esc_url(pccc24_tel('phone_sale')) ?>">
                <span>Bán hàng (vật tư, bình chữa cháy)</span>
                <b><?= pccc24_get_setting('phone_sale') ?></b>
            </a>
            <a class="ios-sheet-call-row" href="<?= esc_url(pccc24_tel('phone_technique')) ?>">
                <span>Kỹ thuật (thi công, bảo trì)</span>
                <b><?= pccc24_get_setting('phone_technique') ?></b>
            </a>
            <a class="ios-sheet-call-row ios-sheet-zalo-row" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">
                <span>Phản hồi nhanh qua Zalo</span>
                <b>Nhắn Zalo ›</b>
            </a>
        </div>

        <form id="quote-form" class="ios-sheet-form" novalidate>
            <div class="ios-sheet-section-label">ĐỂ LẠI SỐ ĐIỆN THOẠI — GỌI LẠI TRONG 15 PHÚT</div>

            <!-- Honeypot chống spam -->
            <div class="sr-only" aria-hidden="true">
                <label for="quote-website">Website</label>
                <input type="text" id="quote-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="ios-sheet-group ios-sheet-inputs">
                <div class="ios-input-row">
                    <label for="quote-name">Tên của bạn *</label>
                    <input type="text" id="quote-name" name="name" required placeholder="Họ và tên hoặc tên đơn vị">
                </div>
                <div class="ios-input-row">
                    <label for="quote-phone">Số điện thoại *</label>
                    <input type="tel" id="quote-phone" name="phone" required inputmode="tel" placeholder="09xxxxxxxx">
                </div>
                <div class="ios-input-row">
                    <label for="quote-need">Nhu cầu tư vấn</label>
                    <select id="quote-need" name="need">
                        <option value="Bình chữa cháy">Bình chữa cháy</option>
                        <option value="Thi công hệ thống">Thi công hệ thống</option>
                        <option value="Bảo trì">Bảo trì</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="ios-input-row">
                    <label for="quote-note">Thiết bị / nội dung cần báo giá</label>
                    <input type="text" id="quote-note" name="note" placeholder="Số lượng dự kiến, tên thiết bị cần báo giá…">
                </div>
            </div>

            <button type="submit" class="btn ios-sheet-submit">Gửi yêu cầu gọi lại</button>
            <p class="ios-sheet-msg" id="quote-msg" role="status" aria-live="polite" hidden></p>
        </form>
    </div>
</dialog>
