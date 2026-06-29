#!/usr/bin/env node
/**
 * Capture wp.org listing screenshots into .wordpress-org/.
 * Requires Docker, `npm run env:start`, and a built admin bundle.
 */
import { execSync } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium, devices } from 'playwright';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const OUT = resolve(ROOT, '.wordpress-org');
const BASE_URL = process.env.SIEVE_BASE_URL ?? 'http://localhost:8888';

function runWp(command) {
	return execSync(command, { cwd: ROOT, encoding: 'utf8', stdio: ['pipe', 'pipe', 'inherit'] });
}

function seedUrls() {
	runWp('npx wp-env run cli wp plugin activate woocommerce sieve');
	runWp('npx wp-env run cli wp eval-file wp-content/plugins/sieve/scripts/seed.php');
	const raw = runWp(
		'npx wp-env run cli wp eval-file wp-content/plugins/sieve/scripts/seed-e2e.php',
	);
	const line = raw
		.split('\n')
		.map((l) => l.trim())
		.find((l) => l.startsWith('{') && l.includes('filterUrl'));
	if (!line) {
		throw new Error(`Seed did not return filterUrl.\n${raw}`);
	}
	return JSON.parse(line);
}

async function main() {
	mkdirSync(OUT, { recursive: true });
	const { filterUrl } = seedUrls();
	const browser = await chromium.launch();

	try {
		const desktop = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		await desktop.goto(filterUrl, { waitUntil: 'networkidle' });
		await desktop.locator('[data-sieve-app]').waitFor();
		await desktop.locator('[data-sieve-results] ul.products li').first().waitFor();
		await desktop.screenshot({ path: resolve(OUT, 'screenshot-1.png') });
		console.log('Wrote screenshot-1.png (faceted filter)');

		const mobile = await browser.newPage({ ...devices['iPhone 13'] });
		await mobile.goto(filterUrl, { waitUntil: 'networkidle' });
		await mobile.locator('[data-sieve-app]').waitFor();
		await mobile.locator('[data-sieve-open]').click();
		await mobile.locator('.sieve-app.is-drawer-open').waitFor();
		await mobile.screenshot({ path: resolve(OUT, 'screenshot-2.png') });
		console.log('Wrote screenshot-2.png (mobile drawer)');

		const admin = await browser.newPage({ viewport: { width: 1400, height: 900 } });
		await admin.goto(`${BASE_URL}/wp-login.php`);
		await admin.fill('#user_login', 'admin');
		await admin.fill('#user_pass', 'password');
		await admin.click('#wp-submit');
		await admin.goto(`${BASE_URL}/wp-admin/admin.php?page=sieve`, { waitUntil: 'networkidle' });
		await admin.locator('#sieve-admin-root h2', { hasText: 'Facets' }).waitFor();
		await admin.screenshot({ path: resolve(OUT, 'screenshot-3.png') });
		console.log('Wrote screenshot-3.png (facet builder)');
	} finally {
		await browser.close();
	}
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
