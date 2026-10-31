import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

// Quét lỗi giao diện: axe-core (WCAG A/AA, mức serious+critical), ảnh vỡ, nút quá nhỏ để chạm, tràn ngang ở 320px.
const PAGES = [
  '/',
  '/san-pham',
  '/san-pham/binh-chua-chay',
  '/san-pham/binh-chua-chay-goc-nuoc-cong-nghe-sinh-hoc-ecosafe/718',
  '/san-pham/tim-kiem?q=b%C3%ACnh',
  '/dich-vu',
  '/tin-tuc',
  '/pages/gioi-thieu',
  '/contacts',
];

for (const url of PAGES) {
  test(`giao diện sạch: ${url}`, async ({ page, isMobile }) => {
    test.setTimeout(120_000);
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.evaluate(async () => {
      for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); }
      window.scrollTo(0, 0);
    });
    await page.waitForTimeout(500);
    const problems: string[] = [];

    const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa'])
      .exclude('.prose') // nội dung bài soạn trong wp-admin (dữ liệu gốc), không phải lỗi theme
      .analyze();
    for (const v of axe.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')) {
      problems.push(`axe ${v.id} (${v.nodes.length}): ${v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' | ')}`);
    }

    const broken = await page.$$eval('img', (imgs) =>
      imgs.filter((i) => i.complete && i.naturalWidth === 0 && i.getAttribute('src')).map((i) => i.getAttribute('src')!));
    for (const src of broken) problems.push(`ảnh vỡ: ${src}`);

    if (isMobile) {
      // Đích chạm tối thiểu 24×24 (WCAG 2.2 AA 2.5.8); bỏ qua link nằm trong đoạn văn và chấm slider (vùng chạm mở rộng bằng ::after).
      const small = await page.$$eval('a[href], button, select, input:not([type=hidden])', (els) =>
        els.filter((el) => {
          const r = el.getBoundingClientRect(), s = getComputedStyle(el);
          if (!r.width || s.visibility === 'hidden' || el.closest('p, li, td, .prose, dialog:not([open]), .sr-only, .slide-dots')) return false;
          return r.width < 24 || r.height < 24;
        }).map((el) => `${el.tagName.toLowerCase()}.${el.className || ''} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 24)}" ${Math.round(el.getBoundingClientRect().width)}×${Math.round(el.getBoundingClientRect().height)}`));
      for (const s of small.slice(0, 8)) problems.push(`đích chạm nhỏ: ${s}`);

      await page.setViewportSize({ width: 320, height: 640 });
      const over = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
      if (over > 0) problems.push(`tràn ngang ${over}px ở màn 320px`);
    }

    expect(problems, problems.join('\n')).toEqual([]);
  });
}
