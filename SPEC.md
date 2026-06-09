# Sieve - Faceted Filter for WooCommerce

Sieve is a faceted product/content filtering plugin for WordPress + WooCommerce. Goal: the same problem space as the leading faceted-search plugins, but dramatically nicer to use and Core-Web-Vitals-native. Vendor: WPPoland.

- FREE edition: WordPress.org, slug `sieve`, GPLv2. MVP first, shipped for testing.
- PRO edition: Freemius, slug `sieve-pro`, extends FREE.
- Repos: `github.com/wppoland/sieve` (public) + `github.com/wppoland/sieve-pro` (private). Local: `~/local/sieve/`, `~/local/sieve-pro/`.
- Architecture mirrors the WPPoland `polski` plugin (PHP 8.1+, DI container, services, REST, blocks, Vite build, wp-env, phpcs/phpstan/Plugin Check).
- Primary locale EN; PL secondary.

## Why we win (differentiation)
Incumbents' real weakness is setup friction, query/index reliability, accessibility, mobile, and add-on gating, not missing features. Sieve is:
1. Zero-config: auto-detect the archive/query loop with a one-click fix; auto-discover field sources (WooCommerce attributes, ACF, Meta Box, Pods, SCF).
2. Accessible: WCAG 2.2 AA out of the box (keyboard, ARIA, focus rings).
3. Mobile-first: slide-in filter drawer with a sticky "Apply / Show N results" bar.
4. CWV-native: server-rendered initial results, in-place replacement (zero CLS), reserved containers, sized images, content-visibility, debounced input, optimistic UI, main-thread yielding.
5. Generous FREE: color and image swatches + range-list buckets included, not gated behind add-ons.
6. Fixes known competitor gaps: price sort with variable products, correct backorder handling, optional variation-level results.

## Architecture
- PHP namespace `Sieve\`, PSR-4 autoload from `src/`.
- `src/`: `Plugin.php` (singleton + boot), `Container.php` (lightweight DI), `Contract/` (HasHooks/Bootable), `Hook/` (admin/frontend/filter hook groups), `Service/` (business logic), `Admin/` (React SPA pages), `Block/` (Gutenberg + WooCommerce Store API), `Rest/` (WP_REST_Controller), `Model/`+`Repository/`+`Enum/`, `Migration/`+`Migrator.php`.
- `config/`: `services.php` (DI bindings), `hooks.php` (boot order), `defaults.php` (default options).
- Core domain:
  - **Facet registry** - pluggable facet types (FacetType interface). MVP types: checkbox, radio, dropdown (searchable), range slider (price/number), search, sort, pager, reset, active-chips.
  - **Index table** - pre-built `wp_sieve_index` (object_id, facet_slug, value, value_num) for fast filtered queries instead of live meta/tax joins; incremental + background re-index.
  - `FilterService` - reads active filters from URL state, modifies the WP/WC query (via the index).
  - `FacetCountService` - cached, dependent counts (counts reflect current selection).
  - `UrlService` - URL state (query params), bookmarkable, back-button-safe (History API).
  - `QueryDetector` - detect the archive/query loop; one-click fix when detection fails.
- Frontend: lean dependency-free vanilla TS (`resources/js/frontend/`), debounced, optimistic, yields to main thread. No jQuery.
- Admin: React SPA (`resources/js/admin/`) - facet builder with live preview.
- Build: Vite + `scripts/build-wp.mjs` -> `build/*.js` + `*.asset.php`; blocks via `@wordpress/scripts`.

## FREE MVP (ship first)
- Facet types: checkbox, radio, dropdown (searchable), range slider, search, sort, pager, reset, active chips + live result count.
- AJAX filtering, no reload; URL state; server-rendered initial results + progressive enhancement.
- WooCommerce: categories, tags, attributes, price, stock status, on-sale; correct backorder handling.
- One "WooCommerce shop" preset (one-click).
- React facet builder with live preview; auto-detect shop/archive loop.
- Mobile filter drawer + sticky apply bar; WCAG 2.2 AA.
- Gutenberg block + shortcode for placement.
- Gates: phpcs (WPCS) + phpstan + Plugin Check green; Playwright e2e (build facet, filter, mobile); CWV smoke (no CLS on filter, INP budget).

## Roadmap
- FREE (post-MVP): hierarchy/tree, color + image swatches, range-list buckets, A-Z, autocomplete; ACF/Meta Box/Pods/SCF auto-discovery; Elementor/Bricks/FSE query-loop bindings; multilingual (WPML/Polylang/TranslatePress) interop.
- PRO: proximity/map facets; advanced templating/listing builder; conditional/dependent facet rules UI; A/B layout testing; performance dashboard + reindex controls; SearchWP/AWS interop; priority support.

## Store + docs (in the plogins monorepo)
- Add one `PluginEntry` to `packages/registry/src/plugins.config.ts`; docs under `apps/docs/src/content/docs/sieve/docs/*` (+ locales). Doc IA: Getting Started, How It Works, Facet Types (one page each), Integrations, Templating, Developers (hooks/JS API/REST/shortcodes), Troubleshooting, FAQ.

## Status
- [ ] Scaffold foundation (this commit)
- [ ] MVP core (index, FilterService, facet types, REST)
- [ ] Admin facet builder (React) + live preview
- [ ] Frontend (AJAX, URL state, mobile drawer, CWV)
- [ ] WooCommerce integration
- [ ] Gates + Playwright + CWV smoke
- [ ] Testable build (FREE MVP)
