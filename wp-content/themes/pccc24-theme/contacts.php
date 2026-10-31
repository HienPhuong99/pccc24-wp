<?php
/**
 * /contacts — cùng phong cách trang chủ. Địa chỉ, GPKD và bản đồ đã có ở footer nên trang này không lặp lại:
 * tập trung vào gọi / nhắn / để lại SĐT, rồi giới thiệu công ty (lấy từ trang "Giới thiệu" để sửa một chỗ).
 */
defined('ABSPATH') || exit;
get_header();
get_template_part('template-parts/crumbs', null, ['items' => [[null, 'Liên hệ']]]);
$about = pccc24_about_data();
$email = pccc24_get_setting('email', false);
$stats = [
    ['50+', 'Kỹ sư chuyên môn cao', 'Có chứng chỉ hành nghề PCCC'],
    ['300+', 'Dự án đã nghiệm thu', 'Nhà xưởng, văn phòng, trường học'],
    ['11+', 'Năm kinh nghiệm', 'Thành lập từ năm 2011'],
    ['100%', 'Thiết bị chính hãng', 'Đạt chuẩn kiểm định PCCC'],
];
?>

<div class="home-title-block">
    <div class="home-eyebrow"><?= pccc24_get_setting('company') ?></div>
    <h1 class="home-title">Liên hệ</h1>
    <p class="home-subtitle">Nhà thầu thi công phòng cháy chữa cháy hàng đầu miền Nam — tư vấn, thi công, bảo trì và cung cấp thiết bị PCCC chính hãng.</p>
</div>

<div class="contact-hero">
    <section class="contact-card" aria-labelledby="ct-quick">
        <h2 id="ct-quick" class="contact-card-title">Gọi hoặc nhắn ngay</h2>
        <p class="contact-card-sub">Kỹ thuật viên tư vấn đúng thiết bị và báo giá trong ngày.</p>
        <div class="contact-rows">
            <a class="contact-row" href="<?= esc_url(pccc24_tel('phone_sale')) ?>">
                <span class="contact-ic"><?= pccc24_icon('phone') ?></span>
                <span class="contact-txt"><small>Bán hàng · Vật tư PCCC</small><b><?= pccc24_get_setting('phone_sale') ?></b></span>
                <span class="chev" aria-hidden="true">›</span>
            </a>
            <a class="contact-row" href="<?= esc_url(pccc24_tel('phone_technique')) ?>">
                <span class="contact-ic"><?= pccc24_icon('phone') ?></span>
                <span class="contact-txt"><small>Kỹ thuật · Thi công &amp; bảo trì</small><b><?= pccc24_get_setting('phone_technique') ?></b></span>
                <span class="chev" aria-hidden="true">›</span>
            </a>
            <a class="contact-row is-zalo" href="<?= esc_url(pccc24_zalo_url()) ?>" target="_blank" rel="noopener">
                <span class="contact-ic"><?= pccc24_icon('chat') ?></span>
                <span class="contact-txt"><small>Zalo · Gửi ảnh thiết bị, nhận báo giá nhanh</small><b>Nhắn Zalo</b></span>
                <span class="chev" aria-hidden="true">›</span>
            </a>
            <a class="contact-row is-mail" href="mailto:<?= esc_attr($email) ?>">
                <span class="contact-ic">@</span>
                <span class="contact-txt"><small>Email · Hồ sơ thầu, báo giá dự án</small><b><?= esc_html($email) ?></b></span>
                <span class="chev" aria-hidden="true">›</span>
            </a>
        </div>
    </section>

    <section class="contact-form-card" aria-labelledby="ct-form">
        <h2 id="ct-form" class="contact-card-title">Để lại số điện thoại</h2>
        <p class="contact-card-sub">Ngoài giờ làm việc? Gửi yêu cầu, kỹ thuật viên sẽ gọi lại báo giá sớm nhất.</p>
        <form data-quote-form novalidate>
            <div class="sr-only" aria-hidden="true">
                <label for="ct-website">Website</label>
                <input type="text" id="ct-website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="ios-sheet-group">
                <div class="ios-input-row">
                    <label for="ct-name">Tên của bạn *</label>
                    <input type="text" id="ct-name" name="name" required placeholder="Họ và tên hoặc tên đơn vị">
                </div>
                <div class="ios-input-row">
                    <label for="ct-phone">Số điện thoại *</label>
                    <input type="tel" id="ct-phone" name="phone" required inputmode="tel" placeholder="09xxxxxxxx">
                </div>
                <div class="ios-input-row">
                    <label for="ct-need">Nhu cầu tư vấn</label>
                    <select id="ct-need" name="need">
                        <option value="Bình chữa cháy">Bình chữa cháy</option>
                        <option value="Thi công hệ thống">Thi công hệ thống</option>
                        <option value="Bảo trì">Bảo trì</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="ios-input-row">
                    <label for="ct-note">Nội dung cần tư vấn</label>
                    <input type="text" id="ct-note" name="note" placeholder="Loại công trình, diện tích, thiết bị cần báo giá…">
                </div>
            </div>
            <button type="submit" class="btn ios-sheet-submit">Gửi yêu cầu gọi lại</button>
            <p class="ios-sheet-msg" role="status" aria-live="polite" hidden></p>
        </form>
    </section>
</div>

<ul class="promises contact-stats">
    <?php foreach ($stats as [$num, $title, $desc]) : ?>
        <li>
            <b class="stat-num"><?= esc_html($num) ?></b>
            <div>
                <b class="promise-title"><?= esc_html($title) ?></b>
                <span class="promise-desc"><?= esc_html($desc) ?></span>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($about['paras']) : ?>
    <section class="section">
        <div class="section-head">
            <h2>Về Hoàng Quân Phát</h2>
            <a href="<?= esc_url($about['url']) ?>">Trang giới thiệu ›</a>
        </div>
        <div class="about-grid">
            <div class="panel about-text">
                <?php foreach ($about['paras'] as $p) : ?>
                    <p><?= esc_html($p) ?></p>
                <?php endforeach; ?>
            </div>
            <?php if ($about['hsnl_url']) : ?>
                <a class="hsnl-card" href="<?= esc_url($about['hsnl_url']) ?>" target="_blank" rel="noopener">
                    <?php if ($about['hsnl_img']) : ?>
                        <img src="<?= esc_url(pccc24_upload_webp($about['hsnl_img'], 250)) ?>" alt="Hồ sơ năng lực Hoàng Quân Phát" width="250" height="350" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <span class="hsnl-txt"><b>Hồ sơ năng lực</b><small>Năng lực thi công, chứng chỉ, dự án đã làm</small></span>
                    <span class="btn btn-call">Xem hồ sơ (PDF)</span>
                </a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($about['projects']) : ?>
    <section class="section">
        <div class="section-head">
            <h2>Khách hàng tiêu biểu</h2>
            <a href="<?= esc_url(home_url('/dich-vu/du-an')) ?>">Tất cả dự án ›</a>
        </div>
        <div class="project-grid">
            <?php foreach ($about['projects'] as $pr) : ?>
                <a class="project-card" href="<?= esc_url($pr['url']) ?>">
                    <img src="<?= esc_url(pccc24_upload_webp($pr['img'], 400)) ?>" alt="" width="400" height="240" loading="lazy" decoding="async">
                    <span><?= esc_html($pr['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($about['certificate'] || $about['partners']) : ?>
    <section class="section">
        <div class="section-head"><h2>Giấy chứng nhận &amp; đối tác</h2></div>
        <div class="cert-grid">
            <?php if ($about['certificate']) : ?>
                <figure class="panel"><img src="<?= esc_url(pccc24_upload_webp($about['certificate'], 1000)) ?>" alt="Giấy chứng nhận đủ điều kiện kinh doanh dịch vụ PCCC và giấy phép đăng ký kinh doanh" width="1000" height="500" loading="lazy" decoding="async"></figure>
            <?php endif; ?>
            <?php if ($about['partners']) : ?>
                <figure class="panel"><figcaption>Đối tác tiêu biểu</figcaption><img src="<?= esc_url(pccc24_upload_webp($about['partners'], 1000)) ?>" alt="Đối tác: Dolphin, Horing Lih, Hochiki, Vinafoam, Shin Yi, FISA Việt Nam, 83MEC" width="1000" height="150" loading="lazy" decoding="async"></figure>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>
