=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, accessible faceted product filtering for WooCommerce: AJAX filters, a mobile drawer, and Core Web Vitals by design.

== Description ==

Sieve gives your shoppers a fast, modern way to find products. They tick a few boxes, drag a price range, pick a rating, and the grid updates instantly with no page reload. It is built to feel effortless and to stay quick on large catalogs, with accessible widgets, a mobile filter drawer, and a rendering approach designed for Core Web Vitals: no layout shift when results change.

Everything runs against a pre-built index, so filtering stays fast even with thousands of products, and the counts next to each option update live as shoppers narrow down.

Filtering that feels instant:

* AJAX filtering with no page reload, and shareable, bookmarkable URLs
* Live dependent counts that update as filters are applied
* Active-filter chips, one-click reset, sorting and pagination built in

Every facet type you need:

* Checkboxes, radio buttons, dropdowns, range sliders, text search, sort, pagination, reset and active-filter chips

Filter by anything in your catalog:

* Categories, tags and product attributes
* Price, stock status, on sale and average rating

Fast and accessible by design:

* Pre-built index for quick filtered queries on large catalogs
* Mobile filter drawer with a sticky Apply bar
* Keyboard and screen-reader friendly widgets
* Core Web Vitals by design: no layout shift when results update

Easy to place and configure:

* Gutenberg "Sieve Filter" block and the `[sieve]` shortcode
* A visual facet builder in the admin: add, reorder and retype facets, set the layout, and rebuild the index

= Sieve PRO =

Sieve PRO adds advanced control and integrations for growing stores:

* Star-rating facet with visual stars
* Conditional facet rules: show or hide facets by category, shop page or customer role
* A/B layout testing to find the filter layout that converts best
* Performance dashboard: index size, catalog coverage and filter-speed benchmarks
* Search integrations: SearchWP and Algolia, with native fallback

Documentation: https://plogins.com/sieve/docs/

== Installation ==

1. Install and activate WooCommerce.
2. Install Sieve and activate it.
3. Open the Sieve menu, rebuild the index, and adjust the facet set if needed.
4. Place the filter on any page with the `[sieve]` shortcode or the "Sieve Filter" block.

== Frequently Asked Questions ==

= Does it require WooCommerce? =
Yes. Sieve filters WooCommerce product archives and any page where you place the filter.

= Does filtering reload the page? =
No. Filtering happens via AJAX with the URL kept in sync so results are shareable.

= How do I add the filter to a page? =
Use the `[sieve]` shortcode or the "Sieve Filter" block. Both render the facets, the results grid, sorting, active-filter chips and pagination together.

== Screenshots ==

1. Faceted filtering on a product page: categories, price range, availability and on-sale facets with live dependent counts, active-filter chips and a results grid.
2. Mobile filter drawer with a sticky "Show results" bar.
3. The facet builder: add, reorder and retype facets, set the layout, and rebuild the index.

== Development ==

The full, human-readable source for the compiled assets is included in this plugin under `resources/`, alongside the build tooling (`package.json`, `scripts/build-wp.mjs`). The compiled files under `build/` are generated from those sources. To rebuild them:

1. `npm install`
2. `npm run build`

This uses Vite (admin and front-end scripts) and @wordpress/scripts (blocks). There is no obfuscation; every shipped asset can be regenerated from the included sources. The public source repository is also available at https://github.com/wppoland/sieve.

== Changelog ==

= 0.1.2 =
* Admin: cleaner facet builder rows (aligned controls, grouped reorder/remove buttons with first/last disabled states, field source shown as a caption).

= 0.1.1 =
* Compliance: added the plugin owner to the Contributors list and included the human-readable sources and build steps for the compiled assets (WordPress.org plugin guidelines).

= 0.1.0 =
* Initial MVP release: pre-built index, AJAX filtering with URL state, dependent facet counts, checkboxes / radio / dropdown / range / search facets, sorting, active-filter chips, pagination, mobile filter drawer, React facet builder, `[sieve]` shortcode and "Sieve Filter" block.
