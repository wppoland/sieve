# WordPress.org submission - Sieve

Submit at: https://wordpress.org/plugins/developers/add/ (logged in as **wppoland**).

## The package

- **Upload this zip:** `/tmp/sieve.zip` (built from a clean trunk, ~46 KB, top folder `sieve/`, no `.DS_Store`).
- Rebuild it any time with: `npm run build && bash scripts/prepare-wporg-release.sh && rm -rf /tmp/sieve-submit && mkdir -p /tmp/sieve-submit/sieve && cp -R /tmp/sieve-wporg-trunk/. /tmp/sieve-submit/sieve/ && (cd /tmp/sieve-submit && zip -rqX /tmp/sieve.zip sieve -x '*.DS_Store')`

## Description to paste in the form

> Sieve adds fast, accessible faceted product filtering to WooCommerce. Shoppers narrow down products instantly with checkboxes, radio, a searchable dropdown, a price range slider, search and sort, without a page reload. Each facet value shows a dependent count, results refresh in place with no layout shift, and there is a mobile filter drawer with a sticky apply bar.
>
> Filtering reads a pre-built index instead of running slow live queries, so it stays fast on large catalogs. The filter is placed with the `[sieve]` shortcode or the "Sieve Filter" block, and a visual facet builder lets you add, reorder and configure facets.
>
> The plugin is GPLv2, makes no external HTTP requests, and stores no personal data. A Pro edition (advanced facet types, conditional rules, A/B layout testing, performance dashboard) is planned and distributed separately; the free plugin is fully functional on its own.

## Pre-submission checklist (all verified 2026-06-09)

- [x] Plugin Check: **0 errors** on a clean release build (only warning was a test-slug textdomain artifact, gone with the real `sieve` slug)
- [x] WPCS (phpcs) clean, PHPStan level 6 clean
- [x] Version consistent: `sieve.php` header + `VERSION` const + readme `Stable tag` all `0.1.0`
- [x] readme.txt: short description < 150 chars, 5 tags, Screenshots section, valid headers
- [x] No external HTTP calls, no tracking, no personal data stored, no `uninstall.php`
- [x] WooCommerce dependency declared (`Requires Plugins: woocommerce`), HPOS + Blocks compat
- [x] Facets auto-build on activation (no "empty index" surprise for reviewers)
- [x] Functional: SSR render + AJAX filter + dependent counts verified in wp-env

## Slug

Requesting slug **`sieve`** (text domain is already `sieve`, so it will match). If the review team assigns a different slug, the text domain and the `/languages` references must be updated to match before the first SVN commit.

## After approval (I can do these once the plugin is approved)

WordPress.org grants SVN access after approval. Then:

1. `svn checkout https://plugins.svn.wordpress.org/sieve /tmp/sieve-svn` (auth: user `wppoland`, password in `~/.claude/secrets/wporg.env` -> `WPORG_SVN_PASSWORD`)
2. `npm run build && bash scripts/prepare-wporg-release.sh`
3. `bash scripts/sync-wporg-svn.sh` (stages trunk + tag `0.1.0` + copies `assets/` icon/banner/screenshots to SVN `/assets`)
4. `cd /tmp/sieve-svn && svn add --force trunk tags assets --auto-props --parents && svn commit -m "Release 0.1.0"`

## Listing assets (already prepared, for SVN /assets)

- `assets/icon-256x256.png`, `assets/banner-772x250.png`
- `assets/screenshot-1.png` (filter on a product page), `screenshot-2.png` (mobile drawer), `screenshot-3.png` (facet builder) - captions in readme `== Screenshots ==`
