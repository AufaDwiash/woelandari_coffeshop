import { createRequire } from 'node:module';
import fs from 'node:fs/promises';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const baseUrl = 'http://localhost/woelandari_coffeshop';
const frameDir = path.resolve('output/demo-gif-frames');
const chromePath = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

await fs.rm(frameDir, { recursive: true, force: true });
await fs.mkdir(frameDir, { recursive: true });

const browser = await chromium.launch({ headless: true, executablePath: chromePath });
const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
page.setDefaultTimeout(8000);

const frames = [];

async function pause(ms = 400) {
  await page.waitForTimeout(ms);
}

async function frame(name, duration = 900) {
  const file = `${String(frames.length + 1).padStart(2, '0')}-${name}.png`;
  await page.screenshot({ path: path.join(frameDir, file), fullPage: false });
  frames.push({ file, duration });
}

async function visit(pathname, name, duration = 900) {
  await page.goto(`${baseUrl}${pathname}`, { waitUntil: 'networkidle' });
  await pause();
  await frame(name, duration);
}

await visit('/', 'home', 1200);

await page.locator('a[href="menu.php"]').first().click();
await page.waitForLoadState('networkidle');
await pause();
await frame('menu-all', 900);

for (const category of ['NON-COFFEE', 'SNACKS', 'MAIN COURSE']) {
  await page.getByText(category, { exact: true }).click();
  await pause(350);
  await frame(`menu-${category.toLowerCase().replaceAll(' ', '-')}`, 700);
}

await visit('/gallery.php', 'gallery', 900);
await page.getByText('BIG EVENTS', { exact: true }).click();
await pause(350);
await frame('events', 800);

await visit('/rating.php', 'rating', 900);
await page.locator('input[name="nama"]').fill('Demo User');
await page.locator('textarea[name="komentar"]').fill('Tampilan website sudah lebih rapi dan mudah digunakan.');
await page.locator('.star-icon').nth(4).click();
await pause(350);
await frame('rating-filled', 900);

await visit('/about.php', 'about', 800);
await visit('/lokasi.php', 'lokasi', 800);

await visit('/login.php', 'login', 900);
await page.locator('input[name="username"]').fill('admin');
await page.locator('input[name="password"]').fill('admin123');
await page.locator('button[type="submit"]').click();
await page.waitForLoadState('networkidle');
await pause();
await frame('admin-dashboard', 1000);

await visit('/admin/feedback.php', 'admin-feedback', 900);
await visit('/admin/gallery_crud.php', 'admin-gallery-crud', 900);
await visit('/admin/menu_crud.php', 'admin-menu-crud', 900);
await visit('/admin/user_manajemen.php', 'admin-users', 900);
await visit('/karyawan/menu_kasir.php', 'kasir', 1000);

await browser.close();
await fs.writeFile(path.join(frameDir, 'frames.json'), JSON.stringify(frames, null, 2));
console.log(frameDir);
