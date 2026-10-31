import { test, expect, Page } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const PRODUCT = '/san-pham/binh-chua-chay-goc-nuoc-cong-nghe-sinh-hoc-ecosafe/718';

// Mỗi trang: không lỗi JS, không PHP warning/notice lọt ra HTML, không cuộn ngang.
async function visit(page: Page, url: string) {
  const errors: string[] = [];
  page.on('pageerror', (e) => errors.push(e.message));
  const res = await page.goto(url);
  const html = await page.content();
  expect(html).not.toMatch(/<b>(Warning|Notice|Deprecated|Fatal error)<\/b>/);
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(overflow).toBeLessThanOrEqual(0);
  return { res, errors };
}

test.describe('Trang công khai (URL gốc pccc24.com)', () => {
  const pages: [string, RegExp][] = [
    ['/', /HOÀNG QUÂN PHÁT/i],
    ['/san-pham', /Thiết bị phòng cháy chữa cháy/],
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
    const quick = page.locator('.grid-products [data-quick]');
    await expect(quick).toHaveCount(20);
    const first = await quick.first().getAttribute('data-quick');
    await page.goto('/san-pham?page=2');
    expect(await quick.first().getAttribute('data-quick')).not.toBe(first);
  });

  test('chọn "Tên Z → A" trên giao diện -> ?sorted=2', async ({ page }) => {
    await page.goto('/san-pham/binh-chua-chay');
    await page.getByLabel('Sắp xếp').selectOption('2');
    await expect(page).toHaveURL(/sorted=2/);
    await expect(page.getByLabel('Sắp xếp')).toHaveValue('2');
  });
});

test.describe('Tương tác người dùng', () => {
  test('tìm kiếm từ ô search ở header', async ({ page }) => {
    await page.goto('/');
    const box = page.getByRole('searchbox', { name: 'Tìm thiết bị' });
    await box.fill('foam');
    await box.press('Enter');
    await expect(page).toHaveURL(/\/san-pham\/tim-kiem\?q=foam/);
    await expect(page.locator('.grid-products .product')).not.toHaveCount(0);
  });

  test('bấm vào ô sản phẩm -> mở trang chi tiết', async ({ page }) => {
    await page.goto('/san-pham/binh-chua-chay');
    await page.locator('.product').first().click(); // giữa ô = vùng ảnh, nằm dưới lớp phủ link
    await expect(page).toHaveURL(/\/san-pham\/[^/]+\/\d+$/);
    await expect(page.locator('.product-detail h1')).toBeVisible();
  });

  test('xem nhanh sản phẩm (popup qua REST)', async ({ page }) => {
    await page.goto('/san-pham/binh-chua-chay');
    await page.getByRole('button', { name: 'Xem nhanh' }).first().click();
    const dlg = page.locator('#quickview');
    await expect(dlg).toBeVisible();
    await expect(dlg.getByRole('link', { name: 'Xem chi tiết' })).toBeVisible({ timeout: 15_000 });
    await expect(dlg.getByRole('link', { name: 'Gọi báo giá' })).toHaveAttribute('href', /^tel:\d{9,}/);
    await page.keyboard.press('Escape');
    await expect(dlg).toBeHidden();
  });

  test('không có giỏ hàng/thanh toán (giống pccc24.com), chỉ gọi/Zalo báo giá', async ({ page, request }) => {
    await page.goto(PRODUCT);
    await expect(page.locator('body')).not.toContainText(/giỏ hàng|đặt hàng|thanh toán/i);
    const tag = page.getByRole('complementary', { name: 'Liên hệ báo giá' });
    await expect(tag.locator('a[href^="tel:"]')).toHaveCount(2);
    await expect(tag.locator('a[href^="https://zalo.me/"]')).toHaveCount(1);
    for (const route of ['orders', 'carts']) {
      const r = await request.post('/wp-json/pccc24/v1/' + route, { data: { name: 'x', phone: '0901234567' } });
      expect(r.status()).toBe(404);
    }
  });

  test('thanh gọi/Zalo cố định ở đáy màn hình mobile + mở iOS Bottom Sheet báo giá & chống spam', async ({ page, request, isMobile }) => {
    test.skip(!isMobile, 'chỉ mobile');
    await page.goto('/san-pham');
    const bar = page.locator('.float-contact');
    const callBtn = bar.getByRole('link', { name: 'Gọi báo giá' });
    await expect(callBtn).toBeInViewport();
    await expect(bar.getByRole('link', { name: 'Zalo' })).toBeInViewport();

    // Bấm "Gọi báo giá" mở iOS Bottom Sheet (#quote-sheet)
    await callBtn.click();
    const sheet = page.locator('#quote-sheet');
    await expect(sheet).toBeVisible();

    // Xoá bộ đếm giới hạn gửi (transient) bằng WP-CLI: API không có cửa reset/giả IP.
    execFileSync('php', ['tools/wp-cli.phar', 'transient', 'delete', '--all', '--quiet']);
    await sheet.locator('#quote-name').fill('Anh Minh Kho Xưởng');
    await sheet.locator('#quote-phone').fill('0982765114');
    await sheet.locator('#quote-need').selectOption('Thi công hệ thống');
    await sheet.getByRole('button', { name: 'Gửi yêu cầu gọi lại' }).click();
    await expect(sheet.locator('#quote-msg')).toContainText(/Đã nhận yêu cầu/i);

    // Kiểm tra honeypot (400) và giới hạn 5 lần/giờ/IP (429)
    const spamRes = await request.post('/wp-json/pccc24/v1/quotes', {
      data: { name: 'Bot', phone: '0901234567', website: 'http://spam.example' },
    });
    expect(spamRes.status()).toBe(400);

    // Đã gửi 1 lần qua form (honeypot không tính) -> thêm 4 lần nữa OK, lần thứ 6 bị chặn.
    for (let i = 2; i <= 5; i++) {
      const okR = await request.post('/wp-json/pccc24/v1/quotes', { data: { name: 'E2E ' + i, phone: '0901234567' } });
      expect(okR.status()).toBe(201);
    }
    const blockedR = await request.post('/wp-json/pccc24/v1/quotes', {
      headers: { 'X-PCCC24-Client': 'gia-ip-khac' },
      data: { name: 'E2E 6', phone: '0901234567' },
    });
    expect(blockedR.status()).toBe(429);
    // Yêu cầu báo giá chứa tên + SĐT khách: không được lộ qua REST công khai.
    expect((await request.get('/wp-json/wp/v2/pccc_quote')).status()).toBe(404);
  });

  test('Chặng A & C + iOS Handoff: trang chủ dùng ảnh nén WebP (< 1,2 MB), Segmented Control chuyển tab không reload, và thẻ kiểm định có rossark-motion-skill', async ({ page }) => {
    let imgBytes = 0;
    page.on('response', async (res) => {
      if (res.request().resourceType() === 'image' && res.status() === 200) {
        try {
          const buf = await res.body();
          imgBytes += buf.byteLength;
        } catch (_) {}
      }
    });
    await page.goto('/', { waitUntil: 'networkidle' });
    expect(imgBytes).toBeLessThan(1_200_000);
    const hasMotion = await page.evaluate(() => Boolean((window as any).PCCC24_MOTION && document.querySelector('[data-motion-tag] .tag-gauge-scene')));
    expect(hasMotion).toBe(true);

    // Kiểm tra iOS Segmented Control trên trang chủ
    const tabs = page.locator('.seg-control [data-seg-tab]');
    expect(await tabs.count()).toBeGreaterThan(1);
    const secondTab = tabs.nth(1);
    const targetId = await secondTab.getAttribute('data-seg-tab');
    await secondTab.click();
    await expect(secondTab).toHaveAttribute('aria-selected', 'true');
    await expect(page.locator('#' + targetId)).toBeVisible();
  });

  test('menu mobile mở được', async ({ page, isMobile }) => {
    test.skip(!isMobile, 'chỉ mobile');
    await page.goto('/');
    await page.getByRole('button', { name: 'Mở menu' }).click();
    const drawer = page.locator('#drawer');
    await expect(drawer).toBeVisible();
    await drawer.getByText('Sản phẩm', { exact: true }).click();
    await expect(drawer.getByRole('link', { name: 'Bình chữa cháy', exact: true })).toBeVisible();
  });

  test('menu "Sản phẩm" desktop mở danh mục', async ({ page, isMobile }) => {
    test.skip(isMobile, 'chỉ desktop');
    await page.goto('/tin-tuc');
    await page.locator('.nav-cats summary').click();
    await page.locator('.nav-cats .cat-list > li > a').first().click();
    await expect(page).toHaveURL(/\/san-pham\/[^/]+$/);
  });
});

test.describe('wp-admin', () => {
  test.skip(({ isMobile }) => isMobile, 'admin test trên desktop');

  test('đăng nhập, sửa metabox sản phẩm, cài đặt PCCC24', async ({ page }) => {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', 'admin');
    await page.fill('#user_pass', process.env.PCCC24_ADMIN_PASS || 'pccc24@admin2026');
    await page.click('#wp-submit');
    await expect(page).toHaveURL(/wp-admin/);

    await page.goto('/wp-admin/edit.php?post_type=pccc_product');
    await expect(page.locator('th#old_id')).toBeVisible();
    await expect(page.locator('#the-list tr')).toHaveCount(20);

    // Metabox sản phẩm: sửa giá rồi lưu, sau đó trả lại như cũ.
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
