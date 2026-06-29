import { test, expect } from '@playwright/test';
import { seedUrls } from './urls';
import type { SeedUrls } from './global-setup';

/**
 * Faceted filter ([sieve]): server-rendered facets, AJAX result swaps with URL
 * state, active-filter chips, and a Core Web Vitals smoke check that filtering
 * does not shift the layout.
 */
test.describe('Faceted filter', () => {
	let urls: SeedUrls;

	test.beforeAll(() => {
		urls = seedUrls();
	});

	test.beforeEach(async ({ page }) => {
		await page.goto(urls.filterUrl);
		await expect(page.locator('[data-sieve-app]')).toBeVisible();
	});

	test('renders facets and a results grid', async ({ page }) => {
		await expect(page.locator('[data-sieve-facets] input[name^="sf_"]').first()).toBeVisible();
		await expect(page.locator('[data-sieve-results] ul.products li').first()).toBeVisible();
	});

	test('checking a facet filters via AJAX, updates the URL and adds a chip', async ({ page }) => {
		const checkbox = page.locator('[data-sieve-facets] input[type="checkbox"][name^="sf_"]').first();
		const name = await checkbox.getAttribute('name');
		const facetKey = (name ?? 'sf_').replace(/\[\]$/, '');

		const [response] = await Promise.all([
			page.waitForResponse((r) => r.url().includes('/sieve/v1/filter') && r.request().method() === 'POST'),
			checkbox.check(),
		]);
		expect(response.ok()).toBeTruthy();

		await expect(page).toHaveURL(new RegExp(facetKey.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
		await expect(page.locator('.sieve-chip[data-sieve-chip]').first()).toBeVisible();
	});

	test('does not shift layout while filtering (CLS smoke)', async ({ page }) => {
		// Start observing layout shifts.
		await page.evaluate(() => {
			(window as unknown as { __cls: number }).__cls = 0;
			new PerformanceObserver((list) => {
				for (const entry of list.getEntries() as unknown as Array<{ value: number; hadRecentInput: boolean }>) {
					if (! entry.hadRecentInput) {
						(window as unknown as { __cls: number }).__cls += entry.value;
					}
				}
			}).observe({ type: 'layout-shift', buffered: true });
		});

		const checkbox = page.locator('[data-sieve-facets] input[type="checkbox"][name^="sf_"]').first();
		await Promise.all([
			page.waitForResponse((r) => r.url().includes('/sieve/v1/filter')),
			checkbox.check(),
		]);
		await page.locator('[data-sieve-results] ul.products').first().waitFor();

		const cls = await page.evaluate(() => (window as unknown as { __cls: number }).__cls);
		// The results container reserves its height during the swap, so a filter
		// interaction should produce a negligible cumulative layout shift.
		expect(cls).toBeLessThan(0.1);
	});
});
