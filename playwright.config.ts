import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/e2e',
  timeout: 60_000,
  retries: 0,
  workers: 1, // php -S chỉ xử lý 1 request mỗi lúc: chạy song song làm test admin chập chờn
  reporter: [['list'], ['html', { open: 'never' }]],
  // PCCC24_BASE_URL=https://... để chạy lại bộ test trên bản đã deploy (kèm PCCC24_ADMIN_PASS lấy từ dist/ADMIN-LOGIN.txt).
  use: { baseURL: process.env.PCCC24_BASE_URL || 'http://localhost:8080', screenshot: 'only-on-failure', trace: 'retain-on-failure' },
  webServer: process.env.PCCC24_BASE_URL ? undefined : { command: 'php -S localhost:8080 -t .', url: 'http://localhost:8080', reuseExistingServer: true, timeout: 60_000 },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 800 } } },
    { name: 'mobile', use: { ...devices['Pixel 7'] } },
  ],
});
