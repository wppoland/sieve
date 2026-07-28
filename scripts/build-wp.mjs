/**
 * WordPress-compatible build for the Sieve admin (React) and frontend (vanilla TS)
 * bundles.
 *
 * Vite's default multi-entry build emits ES modules with bare @wordpress/* imports.
 * Enqueued as classic scripts they throw "Cannot use import statement outside a
 * module" and the React admin never mounts. This builds each entry as a
 * self-contained IIFE, maps the externals to the WordPress browser globals
 * (wp.element, wp.components, ...), and emits *.asset.php with the matching script
 * dependencies for the PHP enqueue.
 *
 * Run: node scripts/build-wp.mjs  (wired as `npm run build:admin`).
 */
import { build } from 'vite';
import { writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const ROOT = resolve(import.meta.dirname || new URL('.', import.meta.url).pathname, '..');

const ENTRIES = {
    admin: 'resources/js/admin/index.tsx',
    'frontend-filter': 'resources/js/frontend/filter.ts',
};

// Bundles that need WordPress React deps in their .asset.php.
const ASSET_PHP = new Set(['admin', 'frontend-filter']);

// Framework-free bundles: emit an empty dependency list.
const VANILLA = new Set(['frontend-filter']);

const GLOBALS = {
    react: 'React',
    'react-dom': 'ReactDOM',
    wp: 'wp',
    '@wordpress/element': 'wp.element',
    '@wordpress/components': 'wp.components',
    '@wordpress/data': 'wp.data',
    '@wordpress/i18n': 'wp.i18n',
    '@wordpress/api-fetch': 'wp.apiFetch',
};

const DEPENDENCIES = ['wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-api-fetch'];

const version = Date.now().toString(16);

for (const [name, entry] of Object.entries(ENTRIES)) {
    await build({
        configFile: false,
        root: ROOT,
        mode: 'production',
        // Classic JSX transform targeting the WordPress element global, so the
        // admin bundle uses wp.element.createElement (external) with no React
        // import and no jsx-runtime resolution.
        esbuild: {
            jsx: 'transform',
            jsxFactory: 'wp.element.createElement',
            jsxFragment: 'wp.element.Fragment',
            jsxDev: false,
            // WordPress only enqueues these bundles on evergreen browsers that
            // support destructuring natively; tell esbuild not to down-level it
            // (esbuild >=0.25 errors instead of transforming it).
            target: 'es2020',
            supported: { destructuring: true },
        },
        define: {
            'process.env.NODE_ENV': JSON.stringify('production'),
            'process.env': '{}',
        },
        resolve: { alias: { '@': resolve(ROOT, 'resources/js') } },
        build: {
            target: 'es2020',
            outDir: 'build',
            emptyOutDir: false,
            manifest: false,
            cssCodeSplit: false,
            lib: {
                entry: resolve(ROOT, entry),
                formats: ['iife'],
                name: `sieve_${name.replace(/-/g, '_')}`,
                fileName: () => `${name}.js`,
            },
            rollupOptions: {
                external: VANILLA.has(name) ? [] : Object.keys(GLOBALS),
                output: {
                    globals: GLOBALS,
                    assetFileNames: `${name}[extname]`,
                },
            },
        },
    });

    if (ASSET_PHP.has(name)) {
        const deps = VANILLA.has(name) ? [] : DEPENDENCIES;
        writeFileSync(
            resolve(ROOT, `build/${name}.asset.php`),
            `<?php return array(\n    'dependencies' => array(${deps.map((d) => `'${d}'`).join(', ')}),\n    'version' => '${version}',\n);\n`,
            'utf8',
        );
    }

    // eslint-disable-next-line no-console
    console.log(`built build/${name}.js (iife)${ASSET_PHP.has(name) ? ` + build/${name}.asset.php` : ''}`);
}
