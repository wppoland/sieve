import { execSync } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * Seeds the e2e demo pages inside the running wp-env and captures their URLs.
 * Runs once before the suite. Requires `npm run env:start` to be up.
 */
export interface SeedUrls {
	filterUrl: string;
	searchUrl: string;
	searchTerm: string;
	searchHasResults: boolean;
}

export const URLS_FILE = resolve(__dirname, '.urls.json');

export default function globalSetup(): void {
	const raw = execSync(
		'npx wp-env run cli wp eval-file wp-content/plugins/sieve/scripts/seed-e2e.php',
		{ cwd: resolve(__dirname, '../..'), encoding: 'utf8' }
	);

	// wp-env prints its own status lines; the seed emits a single JSON line.
	const line = raw
		.split('\n')
		.map((l) => l.trim())
		.find((l) => l.startsWith('{') && l.includes('filterUrl'));

	if (! line) {
		throw new Error(`Seed did not return URLs. Output:\n${ raw }`);
	}

	const urls = JSON.parse(line) as SeedUrls;
	writeFileSync(URLS_FILE, JSON.stringify(urls, null, 2), 'utf8');
	// eslint-disable-next-line no-console
	console.log(`Sieve e2e seeded: ${ urls.filterUrl } | ${ urls.searchUrl }`);
}
