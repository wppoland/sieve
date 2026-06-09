import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright e2e config for Sieve. Targets a running wp-env (npm run env:start)
 * on http://localhost:8888. The global setup seeds deterministic demo pages and
 * writes their URLs to tests/e2e/.urls.json for the specs to consume.
 */
const BASE_URL = process.env.SIEVE_BASE_URL ?? 'http://localhost:8888';

export default defineConfig({
	testDir: './tests/e2e',
	globalSetup: './tests/e2e/global-setup.ts',
	fullyParallel: true,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: process.env.CI ? 'github' : 'list',
	timeout: 30_000,
	expect: { timeout: 7_000 },
	use: {
		baseURL: BASE_URL,
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'chromium', use: { ...devices['Desktop Chrome'] } },
	],
});
