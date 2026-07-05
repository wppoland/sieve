=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.9.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, accessible faceted product filtering for WooCommerce: AJAX filters, a mobile drawer, and Core Web Vitals by design.

== Description ==

Sieve gives your shoppers a fast, modern way to find products. They tick a few boxes, drag a price range, type a keyword, and the grid updates instantly with no page reload. It is built to feel effortless and to stay quick on large catalogs, with accessible widgets, a mobile filter drawer, and a rendering approach designed for Core Web Vitals: no layout shift when results change.

Everything runs against a pre-built index, so filtering stays fast even with thousands of products, and the counts next to each option update live as shoppers narrow down.

Filtering that feels instant:

* AJAX filtering with no page reload, and shareable, bookmarkable URLs
* Live dependent counts that update as filters are applied
* Active-filter chips, one-click reset, sorting and pagination built in

Every facet type you need:

* Checkboxes, radio buttons and searchable dropdowns
* Color and image swatches, hierarchical (tree) categories
* Autocomplete (searchable options) and an A-Z index
* Range sliders, keyword search, sort, pagination and reset

Filter by anything in your catalog:

* Categories, tags and product attributes
* Price, stock status and on sale

Predictive product search:

* An instant typeahead dropdown with thumbnails, prices, SKU and category matches, and full keyboard navigation

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

= You may also like these plugins =

More free WooCommerce plugins from WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - quantity and volume pricing tiers with a server-rendered price table.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) - back-in-stock waitlist that emails shoppers the moment a product returns.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Polish-market compliance: GPSR, Omnibus, GDPR, invoices and storefront modules.

Browse the full catalogue at https://plogins.com/ .

== Installation ==

1. Install and activate WooCommerce.
2. Install Sieve and activate it.
3. Open the Sieve menu, rebuild the index, and adjust the facet set if needed.
4. Place the filter on any page with the `[sieve]` shortcode or the "Sieve Filter" block.

== Frequently Asked Questions ==

= Documentation and links =

* **Documentation** - https://plogins.com/sieve/docs/
* **Plugin page** - https://plogins.com/sieve/
* **Source code** - https://github.com/wppoland/sieve
* **Bug reports and feature requests** - https://github.com/wppoland/sieve/issues
* **Discussions and questions** - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Yes. Sieve filters WooCommerce product archives and any page where you place the filter.

= Does filtering reload the page? =
No. Filtering happens via AJAX with the URL kept in sync so results are shareable.

= What can shoppers filter by? =
Sieve can filter WooCommerce products by categories, tags, attributes, price, stock status, on-sale state and a keyword search field. It also supports range sliders, searchable options, color/image swatches, active-filter chips and sorting.

= Is it fast on large stores? =
Yes. Sieve builds a product filter index, so AJAX filter requests do not need to run slow live joins for every category, attribute and price query.

= How do I add the filter to a page? =
Use the `[sieve]` shortcode or the "Sieve Filter" block. Both render the facets, the results grid, sorting, active-filter chips and pagination together.

= How do I add the predictive search box? =
Use the `[sieve_search]` shortcode or the "Sieve Search" block. As shoppers type, a dropdown shows matching products with thumbnails and prices; it is fully keyboard accessible and falls back to the standard product search when JavaScript is unavailable.

= Is Sieve accessible? =
Yes. The filter UI is built for keyboard use and screen readers, with labelled regions, accessible controls, polite result-count announcements and reduced-motion support.

= Does Sieve work on mobile? =
Yes. Sieve includes a mobile filter drawer with a sticky apply bar, so shoppers can filter products without fighting a long sidebar on small screens.

= Does this plugin work on WordPress Multisite? =

Yes. This plugin is compatible with WordPress Multisite. Network activate it or activate it on individual sites; each site keeps its own settings and data.

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

= 0.9.7 =
* Docs: added a "You may also like" section linking the other free WPPoland WooCommerce plugins. No functional changes.

= 0.9.6 =
* New: Elementor widgets for product search and product filter (works on Elementor 3.x and 4.0).

= 0.9.5 =
* Admin: fix index help text shown on every facet row; add empty-index and empty-facet notices, grouped source picker, save/reindex error messages, and load-failure state.
* Admin: style-preset inline help in Appearance panel.

= 0.9.4 =
* Extension: `sieve_search_product_ids` filter on `SearchResolver` so PRO add-ons can route the in-grid search facet and predictive search through SearchWP or Algolia.

= 0.9.3 =
* Extension: `sieve_facet_body`, `sieve_facet_types` and `sieve_facet_catalog` filters plus `FacetTypeRegistry` in the admin catalog REST response so PRO add-ons can register advanced facet presentations (e.g. star rating).

= 0.9.2 =
* Extension: `sieve_settings` filter and `layout` setting (sidebar, stacked, inline) so PRO add-ons can rotate filter-panel layouts and column counts. FilterEngine applies layout modifier classes on `.sieve-app`.

= 0.9.1 =
* Extension: `sieve_facets` filter and page context (`FacetContext`) so PRO add-ons can show or hide facets by category, shop page or customer role. AJAX requests preserve context via `sf_ctx_*` query vars.

= 0.9.0 =
* Polish: a refreshed, more attractive filter UI. Collapsible facet groups, an "Active filters" chip row with clearer remove buttons, a friendly empty state with a one-click "Clear all filters" action, an accessible loading spinner, and a retryable error message if an update fails.
* Design: themeable CSS custom properties, fluid sizing, automatic dark mode (prefers-color-scheme), and tasteful transitions that respect prefers-reduced-motion. No layout shift when filters apply.
* Admin: inline help on every setting, including a short description of what each facet type looks like to shoppers.
* Accessibility: facet groups expose their expanded/collapsed state, result counts announce politely, the pagination and filter regions are labelled, and remove buttons have clear accessible names.

= 0.8.2 =
* Compliance: documented the public source repository and the build steps for the compiled assets (WordPress.org plugin guidelines).

= 0.8.1 =
* Internationalisation: the admin and front-end JavaScript interface strings are now included in the translation template, so the whole plugin (not just the PHP side) can be fully translated.

= 0.8.0 =
* New: Appearance settings. Choose a style preset (Default, Minimal, Bordered, Soft, Unstyled) and customise the accent, border, muted-text and background colours from the admin, with a live preview and a contrast hint. Applies to both the filter and the predictive search. Zero extra requests, no layout shift, fully backward compatible.

= 0.7.0 =
* Search now behaves as a filter: type to narrow the live grid in place with predictive, diacritic- and typo-tolerant suggestions, combinable with every facet, and URL- and back-button-safe. Dependent facet counts now reflect the active search too.

= 0.6.0 =
* Predictive search is now diacritic-insensitive and typo-tolerant. It matches product titles and SKUs while ignoring diacritic differences (so "lozko" finds "łóżko"), tolerates small typos, and matching categories are found the same way. This release triggers a one-time rebuild of the search index.

= 0.5.0 =
* Predictive search now looks beyond product titles: a partial SKU pass surfaces a product by its code even when the title misses, and matching product categories appear as their own group in the dropdown so a shopper can jump straight to the filtered archive. Results and categories are grouped with headings, and keyboard navigation moves through both.

= 0.4.0 =
* New facet types: Autocomplete (a search box that filters a facet's own options as you type, for facets with many values) and A-Z index (an alphabetical bar that filters options by first letter). Both filter client-side with no extra request, and degrade to a plain option list without JavaScript.

= 0.3.0 =
* New: predictive product search. The `[sieve_search]` shortcode and "Sieve Search" block render an accessible search box with an instant typeahead dropdown (product thumbnails, prices, SKU), full keyboard navigation, and a "view all results" link. Built on WooCommerce product search, loaded as a standalone lightweight bundle so pages without it stay fast.

= 0.2.0 =
* New facet types: color and image swatches (with per-term color/image, plus an automatic color guess from common color names) and hierarchical (tree) category facets that show only branches leading to results.

= 0.1.2 =
* Admin: cleaner facet builder rows (aligned controls, grouped reorder/remove buttons with first/last disabled states, field source shown as a caption).

= 0.1.1 =
* Compliance: added the plugin owner to the Contributors list and included the human-readable sources and build steps for the compiled assets (WordPress.org plugin guidelines).

= 0.1.0 =
* Initial MVP release: pre-built index, AJAX filtering with URL state, dependent facet counts, checkboxes / radio / dropdown / range / search facets, sorting, active-filter chips, pagination, mobile filter drawer, React facet builder, `[sieve]` shortcode and "Sieve Filter" block.
