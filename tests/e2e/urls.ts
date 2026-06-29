import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { SeedUrls } from './global-setup';

/**
 * Reads the URL map written by global setup. Kept in a helper so every spec
 * shares the same deterministic, env-seeded targets.
 */
export function seedUrls(): SeedUrls {
	const file = resolve(__dirname, '.urls.json');
	return JSON.parse(readFileSync(file, 'utf8')) as SeedUrls;
}
