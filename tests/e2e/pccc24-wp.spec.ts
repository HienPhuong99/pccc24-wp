import { test, expect, Page } from '@playwright/test';

const PRODUCT = '/san-pham/binh-chua-chay-goc-nuoc-cong-nghe-sinh-hoc-ecosafe/718';

// Mỗi trang: không lỗi JS, không PHP warning/notice lọt ra HTML.
async function visit(page: Page, url: string) {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  const res = await page.goto(url);
  const html = await page.content();
  expect(html).not.toMatch(/<b>(Warning|Notice|Deprecated|Fatal error)<\/b>/);
  return { res, errors };
}

test.describe('Trang công khai (URL gốc pccc24.com)', () => {
  const pages: [string, RegExp][] = [
    ['/', /HOÀNG QUÂN PHÁT/i],
    ['/san-pham', /Sản phẩm|Bình/],
    ['/san-pham/binh-chua-chay', /Bình chữa cháy/],
    [PRODUCT, /Ecosafe/],
    ['/san-pham/tim-kiem?q=foam', /foam/i],
    ['/dich-vu', /Dịch vụ|THI CÔNG/i],
    ['/dich-vu/thi-cong-lap-dat-he-thong-phong-chay-chua-chay/8', /THI CÔNG/i],
    ['/tin-tuc', /Tin tức|cháy/i],
    ['/pages/gioi-thieu', /Giới thiệu/],
    ['/chinh-sach', /Chính sách/],
    ['/contacts', /Liên hệ/],
  ];
  for (const [url, text] of pages) {
    test(`200 + nội dung: ${url}`, async ({ page }) => {
      const { res, errors } = await visit(page, url);
      expect(res?.status()).toBe(200);
      await expect(page.locator('body')).toContainText(text);
      expect(errors).toEqual([]);
    });
  }

  test('404 cho sản phẩm không tồn tại', async ({ page }) => {
    const res = await page.goto('/san-pham/khong-co/999999');
    expect(res?.status()).toBe(404);
  });

  test('sai slug -> 301 về URL chuẩn', async ({ page }) => {
    await page.goto('/san-pham/sai-slug/718');
    await expect(page).toHaveURL(new RegExp(`${PRODUCT}$`));
  });

  test('ảnh sản phẩm tải được (kể cả tên file tiếng Việt)', async ({ page }) => {
    await page.goto(PRODUCT);
    const broken = await page.evaluate(async () => {
      const imgs = [...document.images].filter((i) => i.src.includes('/wp-content/uploads/'));
      await Promise.all(imgs.map((i) => (i.complete ? null : new Promise((r) => { i.onload = i.onerror = r; }))));
      return { total: imgs.length, broken: imgs.filter((i) => !i.naturalWidth).map((i) => i.src) };
    });
    expect(broken.total).toBeGreaterThan(0);
    expect(broken.broken).toEqual([]);
  });
});

test.describe('Danh mục: phân trang & sắp xếp như CI4', () => {
  test('20 SP/trang và ?page=2 khác trang 1', async ({ page }) => {
    await page.goto('/san-pham');
    const cards = page.locator('.previewProduct');
    await expect(cards).toHaveCount(20);
    const first = await page.locator('[onclick^="previewProduct("]').first().getAttribute('onclick');
    await page.goto('/san-pham?page=2');
    expect(await page.locator('[onclick^="previewProduct("]').first().getAttribute('onclick')).not.toBe(first);
  });

  test('chọn "Z -> A" trên giao diện -> ?sorted=2', async ({ page, isMobile }) => {
    test.skip(isMobile, 'dropdown giống nhau, test 1 lần trên desktop');
    await page.goto('/san-pham/binh-chua-chay');
    await page.locator('.pccc-sort-trigger').click();
    await page.locator('.pccc-sort-item[data-sort-id="2"]').click();
    await expect(page).toHaveURL(/sorted=2/);
  });
});

test.describe('Tương tác người dùng', () => {
  test('tìm kiếm từ ô search ở header', async ({ page, isMobile }) => {
    test.skip(isMobile, 'ô search desktop');
    await page.goto('/');
    const box = page.locator('header input[name="q"]').first();
    await box.fill('foam');
    await box.press('Enter');
    await expect(page).toHaveURL(/\/san-pham\/tim-kiem\?q=foam/);
    await expect(page.locator('[onclick^="previewProduct("]')).not.toHaveCount(0); // nút chỉ hiện khi hover
  });

  test('xem nhanh sản phẩm (popup qua REST)', async ({ page }) => {
    await page.goto('/san-pham/binh-chua-chay');
    await page.locator('[onclick^="previewProduct("]').first().evaluate((el: HTMLElement) => el.click());
    await expect(page.locator('#preview-box')).toBeVisible();
    await expect(page.locator('#ProductPreviewApp')).not.toContainText('Đang tải', { timeout: 15_000 });
    // Nút "Thêm vào giỏ" trong popup phải bấm được thật (không bị widget nổi che).
    await page.locator('#ProductPreviewApp [onclick^="addCart("]').first().click({ trial: true });
  });

  test('thêm vào giỏ -> đặt hàng -> đơn được lưu', async ({ page, request }) => {
    await page.goto('/san-pham/binh-chua-chay');
    await page.locator('[onclick^="addCart("]').first().evaluate((el: HTMLElement) => el.click());
    const modal = page.locator('#pccc24-cart-modal');
    await expect(modal).toBeVisible();
    await expect(page.locator('.countCart').first()).not.toHaveText('0');

    const name = `E2E ${test.info().project.name} ${Date.now()}`;
    const form = page.locator('#pccc24-order-form');
    await form.locator('[name="name"]').fill(name);
    await form.locator('[name="phone"]').fill('0901234567');
    await form.locator('[name="note"]').fill('Đơn kiểm thử tự động');
    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('/wp-json/pccc24/v1/orders')),
      form.locator('button[type="submit"]').click(),
    ]);
    expect(resp.status()).toBe(200);
    const order = await resp.json();
    expect(order.items.length).toBeGreaterThan(0);
    await expect(page.locator('.pccc24-custom-alert-overlay')).toContainText('thành công');
    await expect(page.locator('.countCart').first()).toHaveText('0');
    test.info().annotations.push({ type: 'order_id', description: String(order.id) });
  });

  test('đặt hàng thiếu SĐT bị từ chối (server-side)', async ({ request }) => {
    const r = await request.post('/wp-json/pccc24/v1/orders', { data: { name: 'x', phone: 'abc' } });
    expect(r.status()).toBe(400);
  });

  test('menu mobile mở được', async ({ page, isMobile }) => {
    test.skip(!isMobile, 'chỉ mobile');
    await page.goto('/');
    await page.locator('.show-menu').first().click();
    await expect(page.locator('.menu-mobile')).toBeVisible();
  });
});

test.describe('wp-admin', () => {
  test.skip(({ isMobile }) => isMobile, 'admin test trên desktop');

  test('đăng nhập, xem đơn hàng, sửa metabox sản phẩm, cài đặt PCCC24', async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', process.env.PCCC24_ADMIN_PASS || 'pccc24@admin2026');
    await page.click('#wp-submit');
    await expect(page).toHaveURL(/wp-admin/);

    await page.goto('/wp-admin/edit.php?post_type=pccc_order');
    await expect(page.locator('th#phone')).toBeVisible();

    await page.goto('/wp-admin/edit.php?post_type=pccc_product');
    await expect(page.locator('th#old_id')).toBeVisible();
    await expect(page.locator('#the-list tr')).toHaveCount(20);

    // Metabox sản phẩm: sửa giá rồi lưu, sau đó trả lại như cũ.
    await page.goto('/wp-admin/edit.php?post_type=pccc_product');
    await page.locator('#the-list a.row-title').first().click();
    const price = page.locator('#pccc24_fields input[name="pccc24[price]"]');
    await expect(price).toBeVisible({ timeout: 20_000 });
    const old = await price.inputValue();
    for (const v of ['123000', old]) {
      await price.fill(v);
      await page.evaluate(() => window.scrollTo(0, 0)); // cột phải sticky của WP làm Playwright tính sai viewport
      await page.locator('#publish').click();
      await expect(page.locator('#message.updated')).toBeVisible({ timeout: 20_000 });
      await expect(price).toHaveValue(v);
    }

    await page.goto('/wp-admin/options-general.php?page=pccc24');
    await expect(page.locator('#pccc24_phone')).toHaveValue(/\d{9,}/);
  });
});
