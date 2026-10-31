<?php
/**
 * Footer template (replicated 100% from app/Views/layouts/app.php & support.php)
 */
defined('ABSPATH') || exit;

$footer_menus  = pccc24_get_footer_menus();
$category_tree = pccc24_get_category_tree();
$service_types = pccc24_get_service_types();
$zalo_supports = array_slice(pccc24_get_zalo_supports(), 0, 2);
?>
            </div>
        </div>
    </main>

    <footer class="bg-slate-100 pt-4 pb-4">
        <div class="container-fluid mx-auto">
            <div class="footer-link grid md:grid-cols-4 sm-m:grid-cols-2 justify-between gap-5 mb-6 border-b pb-6">
                <?php foreach ($footer_menus as $footer_type) : ?>
                <div class="item-footer">
                    <p class="font-medium text-xl text-gray-500 mb-2"><?= esc_html($footer_type->name) ?></p>
                    <ul>
                        <?php foreach ($footer_type->pages as $fp) : ?>
                        <li class="mb-2">
                            <a class="text-gray-500 hover:text-gray-700" href="<?= esc_url($fp->url) ?>" title="<?= esc_attr($fp->name) ?>">
                                <?= esc_html($fp->name) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>

                <div class="item-footer">
                    <div class="footer-social">
                        <div class="social">
                            <p class="font-medium text-xl text-gray-500 mb-2">Follow us</p>
                            <div class="flex gap-2 mb-2">
                                <a target="_blank" rel="noopener" href="<?= esc_url(pccc24_get_setting('facebook', false)) ?>" title="Follow us on facebook" style="display:inline-block;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#1877F2"><path d="M12 2.04c-5.5 0-10 4.49-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 0 0 8.44-9.9c0-5.53-4.5-10.02-10-10.02z"/></svg>
                                </a>

                                <a target="_blank" rel="noopener" href="<?= esc_url(pccc24_get_setting('tiktok', false)) ?>" title="Follow us on TikTok" style="display:inline-block;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#010101"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.34-6.34V9.48a8.27 8.27 0 0 0 4.85 1.56V7.64a4.83 4.83 0 0 1-1.09-.95z"/></svg>
                                </a>

                                <a target="_blank" rel="noopener" href="<?= esc_url(pccc24_get_setting('youtube', false)) ?>" title="Follow us on Youtube" style="display:inline-block;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#FF0000"><path d="M21.58 7.19a2.51 2.51 0 0 0-1.77-1.77C18.25 5 12 5 12 5s-6.25 0-7.81.42A2.51 2.51 0 0 0 2.42 7.19 26.38 26.38 0 0 0 2 12a26.38 26.38 0 0 0 .42 4.81 2.51 2.51 0 0 0 1.77 1.77C5.75 19 12 19 12 19s6.25 0 7.81-.42a2.51 2.51 0 0 0 1.77-1.77A26.38 26.38 0 0 0 22 12a26.38 26.38 0 0 0-.42-4.81zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
                                </a>
                            </div>
                            <p class="font-medium text-xl text-gray-500 mb-1">Bản đồ</p>
                            <div style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4902.871758792556!2d106.7338033114599!3d10.871174557392548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317527bcfca46939%3A0x108ef6acd59f3328!2zQ8O0bmcgdHkgUENDQyBIb8OgbmcgUXXDom4gUGjDoXQ!5e1!3m2!1svi!2s!4v1784104195084!5m2!1svi!2s"
                                    width="100%"
                                    height="150"
                                    style="border:0; display:block;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-footer text-center pb-3 text-gray-600">
                <b class="text-lg text-gray-800"><?= pccc24_get_setting('company') ?></b>
                <p>Văn phòng đại diện: <span class="adr"><?= wp_kses_post(pccc24_get_setting('address', false)) ?></span></p>
                <p>Điện thoại: <span class="tel"><?= pccc24_get_setting('phone') ?></span> |
                    Email: <span class="email"><?= pccc24_get_setting('email') ?></span> |
                    Website: <a class="url fn" href="<?= esc_url(home_url('/')) ?>">http://pccc24.com</a></p>
                <p>GPKD: <span class="tel"><?= pccc24_get_setting('gpkd') ?></span></p>
            </div>
        </div>
    </footer>

    <!-- Quick View Modal (#preview-box) -->
    <div id="preview-box" class="hidden fixed inset-0" style="z-index: 2000;">
        <div class="preview-box-content shadow rounded bg-white relative z-50" id="ProductPreviewApp"></div>
        <div onclick="closePreview()" class="fixed inset-0 bg-black opacity-25 z-10"></div>
    </div>

    <!-- Shopping Cart & Order Checkout Modal (#pccc24-cart-modal) -->
    <div id="pccc24-cart-modal" class="hidden fixed inset-0 flex items-center justify-center" style="z-index: 2000;"><!-- trên .main-widget (1000), dưới CustomAlert (9999) -->
        <div onclick="closeCartModal()" class="fixed inset-0 bg-black opacity-40 z-10"></div>
        <div class="bg-white rounded-lg shadow-2xl relative z-20 w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto p-6">
            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="bx bx-cart text-2xl text-red-pccc"></i>
                    <span>Giỏ hàng & Đặt hàng nhanh</span>
                </h2>
                <button type="button" onclick="closeCartModal()" class="text-gray-500 hover:text-red-600 text-2xl leading-none">&times;</button>
            </div>

            <div class="grid md:grid-cols-2 xs-s:grid-cols-1 gap-6">
                <div>
                    <h3 class="font-semibold text-slate-700 mb-2">Danh sách sản phẩm</h3>
                    <div id="pccc24-cart-items" class="max-h-72 overflow-y-auto pr-1"></div>
                    <div class="flex items-center justify-between pt-3 mt-2 border-t font-medium">
                        <span>Tạm tính:</span>
                        <span id="pccc24-cart-total" class="text-amber-600 text-lg">Liên hệ</span>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-slate-700 mb-2">Thông tin nhận hàng</h3>
                    <form id="pccc24-order-form" class="grid gap-3">
                        <input type="text" name="name" placeholder="Họ tên người nhận (*)" required class="w-full px-3 py-2 rounded border hover:border-slate-700" />
                        <input type="text" name="phone" placeholder="Số điện thoại liên hệ (*)" required class="w-full px-3 py-2 rounded border hover:border-slate-700" />
                        <input type="email" name="email" placeholder="Email (nếu có)" class="w-full px-3 py-2 rounded border hover:border-slate-700" />
                        <input type="text" name="address" placeholder="Địa chỉ giao hàng" class="w-full px-3 py-2 rounded border hover:border-slate-700" />
                        <textarea name="note" rows="3" placeholder="Ghi chú đơn hàng / yêu cầu báo giá..." class="w-full px-3 py-2 rounded border hover:border-slate-700"></textarea>
                        <div class="flex justify-end gap-2 mt-1">
                            <button type="button" onclick="closeCartModal()" class="px-4 py-2 rounded border hover:bg-slate-100">Tiếp tục xem</button>
                            <button type="submit" class="px-5 py-2 rounded bg-red-pccc hover:bg-rose-700 text-white font-medium cursor-pointer">Đặt hàng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer (.menu-mobile) - Always rendered for responsive & mobile devices -->
    <div class="menu-mobile hidden fixed inset-0 z-50">
        <div class="bg-white w-64 h-full overflow-auto relative z-30">
            <div class="p-2">
                <a href="<?= esc_url(home_url('/')) ?>">
                    <img src="<?= esc_url(pccc24_get_setting('logo', false)) ?>" alt="Logo" />
                </a>
            </div>
            <ul class="w-64 mt-1">
                <li class="px-2 py-1.5 border-b">
                    <div class="flex items-center justify-between cursor-pointer" id="toggle-menu-ct-mobile">
                        <span class="text-base font-medium">Sản phẩm</span>
                        <span class="show-more-ct-mobile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </span>
                    </div>
                    <ul class="menu-ct-mobile mt-1.5 pl-2 hidden">
                        <?php foreach ($category_tree as $ct_root) : ?>
                        <li class="ct-1 py-1.5 li-parent">
                            <div class="flex justify-between items-center">
                                <a href="<?= esc_url($ct_root->url) ?>">
                                    <span><?= esc_html($ct_root->name) ?></span>
                                </a>
                                <?php if (!empty($ct_root->childrens)) : ?>
                                <span class="show-ct-child-mobile cursor-pointer p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($ct_root->childrens)) : ?>
                            <ul class="hidden pl-2 mt-1.5">
                                <?php foreach ($ct_root->childrens as $ct_child) : ?>
                                <li class="py-1.5">
                                    <a class="text-slate-500" href="<?= esc_url($ct_child->url) ?>">
                                        <?= esc_html($ct_child->name) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php foreach ($service_types as $st) : ?>
                <li class="px-2 py-1.5 border-b">
                    <a class="text-base font-medium" href="<?= esc_url($st->url) ?>">
                        <?= esc_html($st->name) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <li class="px-2 py-1.5 border-b">
                    <a class="text-base font-medium" href="<?= esc_url(home_url('/tin-tuc')) ?>">Tin tức</a>
                </li>
                <li class="px-2 py-1.5 border-b">
                    <a class="text-base font-medium" href="<?= esc_url(home_url('/contacts')) ?>">Liên hệ</a>
                </li>
            </ul>
            <div class="grid gap-2 mb-4 mt-2 p-2 text-sm">
                <p>
                    <span class="text-gray-500">Văn phòng đại diện: </span>
                    <span><?= wp_kses_post(pccc24_get_setting('address', false)) ?></span>
                </p>
                <p>
                    <span class="text-gray-500">Điện thoại:</span>
                    <span><?= pccc24_get_setting('phone') ?></span>
                </p>
                <p>
                    <span class="text-gray-500">Hỗ trợ bán hàng:</span>
                    <span><?= pccc24_get_setting('phone_sale') ?></span>
                </p>
                <p>
                    <span class="text-gray-500">Hỗ trợ kỹ thuật:</span>
                    <span><?= pccc24_get_setting('phone_technique') ?></span>
                </p>
                <p>
                    <span class="text-gray-500">Email: </span>
                    <span><?= pccc24_get_setting('email') ?></span>
                </p>
            </div>
        </div>
        <div onclick="closeMenu()" class="fixed inset-0 bg-black opacity-25 z-10"></div>
    </div>

    <!-- Floating Support Widget (layouts/support.php) -->
    <div class="main-widget">
        <div class="def-content element unsee">
            <div class="def-header">
                Liên hệ với chúng tôi
                <div class="close-icon" title="Đóng">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                    </svg>
                </div>
            </div>
            <div class="py-2" id="SupportApp"></div>
        </div>
        <div class="out-circle cursor-pointer">
            <div class="main-icon">
                <svg width="20" height="20" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(-825 -308)">
                        <g>
                            <path transform="translate(825 308)" fill="#FFFFFF" d="M 19 4L 17 4L 17 13L 4 13L 4 15C 4 15.55 4.45 16 5 16L 16 16L 20 20L 20 5C 20 4.45 19.55 4 19 4ZM 15 10L 15 1C 15 0.45 14.55 0 14 0L 1 0C 0.45 0 0 0.45 0 1L 0 15L 4 11L 14 11C 14.55 11 15 10.55 15 10Z"></path>
                        </g>
                    </g>
                </svg>
                <p>Hỗ trợ</p>
            </div>
            <div class="close-icon element unsee">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="zalo-live-fixed">
        <div class="gap-2 grid">
            <?php foreach ($zalo_supports as $index => $support) : ?>
            <a href="<?= esc_url('https://zalo.me/' . str_replace(' ', '', $support->content)) ?>" title="Zalo" target="_blank" rel="noopener">
                <img src="<?= esc_url(pccc24_theme_asset('images/zalonew_' . ($index + 1) . '.png')) ?>" alt="Chat zalo" style="width: 100px; height: auto;">
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="button" aria-label="Lên đầu trang" class="scroll-top scroll-to-target" data-target="html">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"></path>
        </svg>
    </button>

    <?php wp_footer(); ?>
</body>
</html>
