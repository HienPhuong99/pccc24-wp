import { test, expect, Page } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

// Thêm / sửa / xoá qua giao diện wp-admin như người thật, rồi đối chiếu ngoài site. Mỗi test tự dọn dữ liệu nó tạo.
test.describe.configure({ mode: 'serial' });
test.skip(({ isMobile }) => isMobile, 'wp-admin test trên desktop');

const uid = () => `E2E ${Date.now().toString(36)}`;
const slugOf = (s: string) => s.toLowerCase().replace(/\s+/g, '-');

async function login(page: Page) {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', process.env.PCCC24_ADMIN_PASS || 'pccc24@admin2026');
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/wp-admin/);
}

async function publish(page: Page) {
  await page.evaluate(() => window.scrollTo(0, 0)); // cột phải sticky của WP làm Playwright tính sai viewport
  // WP tự lưu nháp định kỳ và khoá nút trong lúc đó -> cú bấm có thể bị bỏ qua, bấm lại tới khi lưu xong.
  await expect(async () => {
    await page.locator('#publish:not(.disabled)').click();
    await expect(page.locator('#message.updated')).toBeVisible({ timeout: 8_000 });
  }).toPass({ timeout: 45_000 });
}

type Fields = Record<string, string>;

async function fillPost(page: Page, title: string, content: string | null, fields: Fields, menuOrder?: string) {
  await page.locator('#title').fill(title);
  if (content !== null) {
    await page.locator('#content-html').click(); // tab "Mã" của trình soạn thảo cổ điển
    await page.locator('#content').fill(content);
  }
  for (const [k, v] of Object.entries(fields)) await page.locator(`[name="pccc24[${k}]"]`).fill(v);
  if (menuOrder !== undefined) await page.locator('#menu_order').fill(menuOrder);
}

async function create(page: Page, type: string, title: string, content: string | null, fields: Fields = {}, menuOrder?: string) {
  await page.goto(`/wp-admin/post-new.php?post_type=${type}`);
  await fillPost(page, title, content, fields, menuOrder);
  return page;
}

const postId = (page: Page) => Number(new URL(page.url()).searchParams.get('post'));

async function update(page: Page, id: number, title: string, fields: Fields = {}) {
  await page.goto(`/wp-admin/post.php?post=${id}&action=edit`);
  await fillPost(page, title, null, fields);
  await publish(page);
  await expect(page.locator('#title')).toHaveValue(title);
}

// Chuyển vào thùng rác rồi xoá vĩnh viễn, đúng 2 bước như người dùng làm trong danh sách.
async function trashAndDelete(page: Page, type: string, id: number) {
  await page.goto(`/wp-admin/edit.php?post_type=${type}`);
  await page.locator(`#post-${id}`).hover();
  await page.locator(`#post-${id} .row-actions .trash a`).click();
  await expect(page.locator('#message.updated')).toContainText(/thùng rác/i);
  await page.goto(`/wp-admin/edit.php?post_status=trash&post_type=${type}`);
  await page.locator(`#post-${id}`).hover();
  await page.locator(`#post-${id} .row-actions .delete a`).click();
  await expect(page.locator('#message.updated')).toContainText(/đã được xóa/i);
  await expect(page.locator(`#post-${id}`)).toHaveCount(0);
}

test.beforeEach(async ({ page }) => {
  page.on('dialog', (d) => d.accept()); // hộp xác nhận "Bạn có chắc muốn xoá?"
  await login(page);
});

test('Sản phẩm: thêm (kèm ảnh, danh mục, giá, thông số) → sửa → xoá, đối chiếu ngoài site', async ({ page }) => {
  const name = uid(), img = `${slugOf(name)}.png`;
  await create(page, 'pccc_product', name, '<p>Mô tả thử nghiệm E2E.</p>', {
    price: '1234000', specs: 'Trọng lượng | 4 kg\nXuất xứ | Việt Nam',
  });
  await page.locator('#pccc_product_catchecklist label', { hasText: 'Bình chữa cháy gốc nước' }).locator('input').check();

  // Ảnh đại diện qua thư viện media.
  await page.locator('#set-post-thumbnail').click();
  await page.locator('.media-modal #menu-item-upload').click();
  await page.locator('.media-modal input[type=file]').setInputFiles({
    name: img, mimeType: 'image/png', buffer: readFileSync('wp-content/themes/pccc24-theme/assets/images/no-image.png'),
  });
  await expect(page.locator('.media-modal .attachment.selected.save-ready')).toBeVisible({ timeout: 30_000 });
  await page.locator('.media-modal .media-button-select').click();
  await expect(page.locator('#postimagediv img')).toBeVisible();
  await publish(page);
  const id = postId(page);

  // Ngoài site: URL kiểu cũ /san-pham/{slug}/{id}, giá, thông số, ảnh, danh mục; tìm kiếm ra.
  const url = `/san-pham/${slugOf(name)}/${id}`;
  await page.goto(url);
  await expect(page.locator('h1')).toHaveText(name);
  await expect(page.locator('.product-detail .product-price')).toHaveText('1.234.000đ');
  await expect(page.locator('.specs')).toContainText('Xuất xứ');
  await expect(page.locator('.main-img img')).toHaveAttribute('src', new RegExp(slugOf(name)));
  await expect(page.locator('.crumbs, nav[aria-label]').first()).toContainText('Bình chữa cháy gốc nước');
  await page.goto(`/san-pham/tim-kiem?q=${encodeURIComponent(name)}`);
  await expect(page.locator('.product h3', { hasText: name })).toBeVisible();

  // Sửa tên + giá → URL theo slug mới, giá mới; URL slug cũ 301 về URL mới.
  const renamed = `${name} sửa`;
  await update(page, id, renamed, { price: '0' });
  // Đường dẫn (slug) đổi qua "Sửa nhanh" ở danh sách, như admin vẫn làm.
  await page.goto('/wp-admin/edit.php?post_type=pccc_product');
  await page.locator(`#post-${id}`).hover();
  await page.locator(`#post-${id} .row-actions button.editinline`).click();
  await page.locator(`#edit-${id} input[name=post_name]`).fill(slugOf(renamed));
  await page.locator(`#edit-${id} button.save`).click();
  await expect(page.locator(`#edit-${id}`)).toHaveCount(0);
  await page.goto(`/san-pham/${slugOf(renamed)}/${id}`);
  await expect(page.locator('h1')).toHaveText(renamed);
  await expect(page.locator('.product-detail .product-price')).toContainText('liên hệ');
  const old = await page.request.get(url, { maxRedirects: 0 });
  expect(old.status()).toBe(301);

  await trashAndDelete(page, 'pccc_product', id);
  expect((await page.request.get(`/san-pham/${slugOf(renamed)}/${id}`)).status()).toBe(404);

  // Dọn ảnh vừa tải lên (xoá vĩnh viễn trong Thư viện).
  await page.goto(`/wp-admin/upload.php?mode=list&s=${encodeURIComponent(slugOf(name))}`);
  const row = page.locator('#the-list tr', { hasText: slugOf(name) }).first();
  await row.hover();
  await row.locator('.row-actions .delete a').click();
  await expect(page.locator('#the-list tr', { hasText: slugOf(name) })).toHaveCount(0);
});

test('Dịch vụ, Tin tức, Trang: thêm → sửa → xoá, đối chiếu ngoài site', async ({ page }) => {
  for (const [type, pathOf] of [
    ['pccc_service', (s: string, id: number) => `/dich-vu/${s}/${id}`],
    ['post', (s: string, id: number) => `/tin-tuc/${s}/${id}`],
    ['page', (s: string) => `/pages/${s}`],
  ] as const) {
    const name = uid();
    await create(page, type, name, `<p>Nội dung ${type} E2E.</p>`);
    await publish(page);
    const id = postId(page);
    // Lấy đúng URL ngoài site từ ô "Đường dẫn" của WP rồi so với mẫu URL cũ của pccc24.com.
    const link = await page.locator('#edit-slug-box a').first().getAttribute('href');
    expect(new URL(link!).pathname).toBe(pathOf(slugOf(name), id));
    await page.goto(link!);
    await expect(page.locator('h1')).toHaveText(name);
    await expect(page.locator('.prose, article').first()).toContainText(`Nội dung ${type} E2E.`);

    const renamed = `${name} sửa`;
    await update(page, id, renamed);
    await page.goto(link!);
    await expect(page.locator('h1')).toHaveText(renamed);

    await trashAndDelete(page, type, id);
    expect((await page.request.get(link!)).status()).toBe(404);
  }
});

test('Slider, Chính sách, Hỗ trợ viên: thêm → sửa → xoá, đối chiếu trang chủ / REST', async ({ page }) => {
  // Slider: hiện trên banner trang chủ (alt = tiêu đề).
  const slide = uid();
  await create(page, 'pccc_slider', slide, null, { url: '/san-pham' }, '-1');
  await publish(page);
  const sid = postId(page);
  await page.goto('/');
  await expect(page.locator(`.slides img[alt="${slide}"]`)).toHaveCount(1);
  await update(page, sid, `${slide} sửa`);
  await page.goto('/');
  await expect(page.locator(`.slides img[alt="${slide} sửa"]`)).toHaveCount(1);
  await trashAndDelete(page, 'pccc_slider', sid);
  await page.goto('/');
  await expect(page.locator(`.slides img[alt^="${slide}"]`)).toHaveCount(0);

  // Chính sách vị trí 1: dải cam kết trang chủ (đặt thứ tự -1 để lên đầu).
  const pol = uid();
  await create(page, 'pccc_policy', pol, '<p>Cam kết E2E</p>', { position: '1' }, '-1');
  await publish(page);
  const pid = postId(page);
  await page.goto('/');
  await expect(page.getByText(pol, { exact: true })).toBeVisible();
  await trashAndDelete(page, 'pccc_policy', pid);
  await page.goto('/');
  await expect(page.getByText(pol, { exact: true })).toHaveCount(0);

  // Hỗ trợ viên: API /supports (popup hỗ trợ dùng).
  const sup = uid();
  await create(page, 'pccc_support', sup, null, { content: '0900000000', support_type: 'phone' });
  await publish(page);
  const uidSup = postId(page);
  const names = async () => ((await (await page.request.get('/wp-json/pccc24/v1/supports')).json()).supports as { name: string }[]).map((s) => s.name);
  expect(await names()).toContain(sup);
  await update(page, uidSup, `${sup} sửa`, { content: '0911111111' });
  expect(await names()).toContain(`${sup} sửa`);
  await trashAndDelete(page, 'pccc_support', uidSup);
  expect(await names()).not.toContain(`${sup} sửa`);
});

test('Danh mục sản phẩm: thêm → sửa → xoá, đối chiếu /san-pham/{slug}', async ({ page }) => {
  const name = uid(), slug = slugOf(name);
  await page.goto('/wp-admin/edit-tags.php?taxonomy=pccc_product_cat&post_type=pccc_product');
  await page.locator('#tag-name').fill(name);
  await page.locator('#tag-slug').fill(slug);
  await page.locator('#parent').selectOption({ label: 'Bình chữa cháy' });
  await page.locator('#submit').click();
  await expect(page.locator('#the-list tr', { hasText: name })).toBeVisible();

  await page.goto(`/san-pham/${slug}`);
  await expect(page.locator('h1')).toContainText(name);

  await page.goto('/wp-admin/edit-tags.php?taxonomy=pccc_product_cat&post_type=pccc_product&s=' + encodeURIComponent(name));
  await page.locator('#the-list a.row-title', { hasText: name }).click();
  await page.locator('#name').fill(`${name} sửa`);
  await page.locator('#edittag input[type=submit].button-primary').click();
  await expect(page.locator('#message')).toBeVisible();
  await page.goto(`/san-pham/${slug}`);
  await expect(page.locator('h1')).toContainText(`${name} sửa`);

  await page.goto('/wp-admin/edit-tags.php?taxonomy=pccc_product_cat&post_type=pccc_product&s=' + encodeURIComponent(name));
  await page.locator('#the-list a.row-title', { hasText: name }).click();
  await page.locator('#delete-link a').click();
  await expect(page).toHaveURL(/edit-tags\.php/);
  expect((await page.request.get(`/san-pham/${slug}`)).status()).toBe(404);
});

test('Yêu cầu báo giá: khách gửi → admin xem chi tiết → xoá', async ({ page }) => {
  execFileSync('php', ['tools/wp-cli.phar', 'transient', 'delete', '--all', '--quiet']); // xoá bộ đếm 5 lần/giờ
  const name = uid();
  const res = await page.request.post('/wp-json/pccc24/v1/quotes', {
    data: { name, phone: '0987654321', need: 'Bảo trì', note: 'Ghi chú E2E', source_url: 'http://localhost:8080/' },
  });
  expect(res.status()).toBe(201);
  const { id } = await res.json();

  await page.goto('/wp-admin/edit.php?post_type=pccc_quote');
  await expect(page.locator(`#post-${id} .row-title`)).toContainText(`${name} — 0987654321 (Bảo trì)`);
  await page.locator(`#post-${id} .row-title`).click();
  await expect(page.locator('[name="pccc24[phone]"]')).toHaveValue('0987654321');
  await expect(page.locator('[name="pccc24[note]"]')).toHaveValue('Ghi chú E2E');
  await trashAndDelete(page, 'pccc_quote', id);
});

test('Cài đặt PCCC24: sửa số bán hàng → header ngoài site đổi theo → trả lại', async ({ page }) => {
  await page.goto('/wp-admin/options-general.php?page=pccc24');
  const field = page.locator('#pccc24_phone_sale');
  const old = await field.inputValue();
  for (const v of ['0909123456', old]) {
    await page.goto('/wp-admin/options-general.php?page=pccc24');
    await field.fill(v);
    await page.locator('#submit').click();
    await expect(page.locator('#setting-error-settings_updated')).toBeVisible();
    await page.goto('/');
    await expect(page.locator(`a[href="tel:${v}"]`).first()).toBeAttached();
  }
});
