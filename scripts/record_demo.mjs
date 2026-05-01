import { createRequire } from 'node:module';
import fs from 'node:fs/promises';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const baseUrl = 'http://localhost/woelandari_coffeshop';
const outputDir = path.resolve('output/playwright');
const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

await fs.mkdir(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true, executablePath: chromePath });
const context = await browser.newContext({
  viewport: { width: 1366, height: 768 },
  recordVideo: {
    dir: outputDir,
    size: { width: 1366, height: 768 },
  },
});

const page = await context.newPage();
page.setDefaultTimeout(8000);

const pause = (ms = 1000) => page.waitForTimeout(ms);

async function visit(pathname, delay = 1000) {
  await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'networkidle' });
  await pause(delay);
}

await visit('/', 1300);
await page.locator('a[href="menu.php"]').first().click();
await page.waitForLoadState('networkidle');
await pause(1000);

await page.getByText('NON-COFFEE', { exact: true }).click();
await pause(700);
await page.getByText('SNACKS', { exact: true }).click();
await pause(700);
await page.getByText('MAIN COURSE', { exact: true }).click();
await pause(700);
await page.getByText('ALL UNITS', { exact: true }).click();
await pause(700);
await page.mouse.wheel(0, 550);
await pause(900);

await visit('/gallery.php', 900);
await page.getByText('BIG EVENTS', { exact: true }).click();
await pause(1000);
await page.getByText('COLLECTION', { exact: true }).click();
await pause(800);

await visit('/rating.php', 900);
await page.locator('input[name="nama"]').fill('Demo Visitor');
await page.locator('textarea[name="komentar"]').fill('Demo rekaman fitur rating.');
await page.locator('.star-icon[data-val="5"]').click();
await pause(1300);

await visit('/about.php', 900);
await page.mouse.wheel(0, 600);
await pause(900);

await visit('/community.php', 900);
await page.mouse.wheel(600, 0);
await pause(900);

await visit('/lokasi.php', 900);

await visit('/login.php', 800);
await page.locator('input[name="username"]').fill('admin');
await page.locator('input[name="password"]').fill('admin123');
await page.locator('button[name="login"]').click();
await page.waitForLoadState('networkidle');
await pause(1200);

await visit('/admin/dashboard.php', 1000);
await visit('/admin/feedback.php', 1000);
await visit('/admin/gallery_crud.php', 1000);
await page.getByText('Kelola Event', { exact: true }).click();
await page.waitForLoadState('networkidle');
await pause(900);
await visit('/admin/menu_crud.php', 900);
await page.getByText('+ TAMBAH MENU BARU', { exact: true }).click();
await pause(900);
await visit('/admin/user_manajemen.php', 900);

await visit('/karyawan/menu_kasir.php', 900);
await page.getByText('CAFE LATTE', { exact: true }).click();
await pause(500);
await page.getByText('ESPRESSO', { exact: true }).click();
await pause(1200);

await page.goto(`${baseUrl}/logout.php`, { waitUntil: 'networkidle' });
await pause(800);

const video = page.video();
await context.close();
await browser.close();

const videoPath = await video.path();
const finalPath = path.join(outputDir, `woelandari-demo-${Date.now()}.webm`);
await fs.rename(videoPath, finalPath);
console.log(finalPath);
