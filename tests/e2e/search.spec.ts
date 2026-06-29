import { test, expect } from '@playwright/test';
import { seedUrls } from './urls';
import type { SeedUrls } from './global-setup';

/**
 * Predictive search ([sieve_search]): the typeahead dropdown, its ARIA combobox
 * behaviour, keyboard navigation and graceful degradation.
 */
test.describe('Predictive search', () => {
	let urls: SeedUrls;

	test.beforeAll(() => {
		urls = seedUrls();
	});

	test.beforeEach(async ({ page }) => {
		test.skip(! urls.searchHasResults, 'No products match the seed term in this env');
		await page.goto(urls.searchUrl);
	});

	test('opens a dropdown of products as you type', async ({ page }) => {
		const input = page.locator('[data-sieve-search-input]');
		await expect(input).toHaveAttribute('role', 'combobox');
		await expect(input).toHaveAttribute('aria-expanded', 'false');

		await input.fill(urls.searchTerm);

		const results = page.locator('[data-sieve-search-results]');
		await expect(results).toBeVisible();
		await expect(input).toHaveAttribute('aria-expanded', 'true');
		await expect(results.locator('.sieve-search__item').first()).toBeVisible();
		await expect(results.locator('.sieve-search__all')).toBeVisible();
	});

	test('keyboard navigation tracks the active option', async ({ page }) => {
		const input = page.locator('[data-sieve-search-input]');
		await input.fill(urls.searchTerm);
		await expect(page.locator('.sieve-search__item').first()).toBeVisible();

		await input.press('ArrowDown');
		const firstOption = page.locator('.sieve-search__item').first();
		await expect(firstOption).toHaveClass(/is-active/);
		await expect(firstOption).toHaveAttribute('aria-selected', 'true');

		const optionId = await firstOption.getAttribute('id');
		await expect(input).toHaveAttribute('aria-activedescendant', optionId ?? '');
	});

	test('Escape closes the dropdown', async ({ page }) => {
		const input = page.locator('[data-sieve-search-input]');
		await input.fill(urls.searchTerm);
		const results = page.locator('[data-sieve-search-results]');
		await expect(results).toBeVisible();

		await input.press('Escape');
		await expect(results).toBeHidden();
		await expect(input).toHaveAttribute('aria-expanded', 'false');
	});

	test('Enter on the active option navigates to the product', async ({ page }) => {
		const input = page.locator('[data-sieve-search-input]');
		await input.fill(urls.searchTerm);
		await expect(page.locator('.sieve-search__item').first()).toBeVisible();

		const href = await page.locator('.sieve-search__item').first().getAttribute('href');
		await input.press('ArrowDown');
		await input.press('Enter');

		await page.waitForURL(href ?? '**');
		expect(page.url()).toBe(href);
	});

	test('below the minimum length shows nothing', async ({ page }) => {
		const input = page.locator('[data-sieve-search-input]');
		await input.fill('h');
		// Give the debounce a moment; the dropdown must stay closed.
		await page.waitForTimeout(400);
		await expect(page.locator('[data-sieve-search-results]')).toBeHidden();
	});
});
