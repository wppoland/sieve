=== Sieve - Faceted Filter for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, accessible faceted product filtering for WooCommerce: AJAX filters, a mobile drawer, and Core Web Vitals by design.

== Description ==

Sieve lets shoppers narrow down products instantly with checkboxes, ranges, search and sort, without a page reload. It is built to be pleasant to use and fast: accessible widgets, a mobile filter drawer, and a rendering approach designed for Core Web Vitals (no layout shift when results update).

* AJAX filtering with no page reload, with shareable, bookmarkable URLs
* Predictive product search: an instant typeahead dropdown with thumbnails, prices, SKU and category matches, and keyboard navigation
* Facet types: checkboxes, radio, searchable dropdown, color and image swatches, hierarchical (tree) categories, autocomplete (searchable options), A-Z index, range slider, search, sort, pagination, reset, active-filter chips
* WooCommerce: categories, tags, attributes, price, stock status, on sale
* A pre-built index for fast filtered queries on large catalogs
* Mobile filter drawer with a sticky Apply bar
* Accessible widgets (keyboard and screen-reader friendly)
* Gutenberg blocks and shortcodes for placement

This is an early release (MVP). Documentation: https://plogins.com/sieve/docs/

== Installation ==

1. Install and activate WooCommerce.
2. Install Sieve and activate it.
3. Open the Sieve menu, rebuild the index, and adjust the facet set if needed.
4. Place the filter on any page with the `[sieve]` shortcode or the "Sieve Filter" block.

== Frequently Asked Questions ==

= Does it require WooCommerce? =
Yes, this early version targets WooCommerce product archives.

= Does filtering reload the page? =
No. Filtering happens via AJAX with the URL kept in sync so results are shareable.

= How do I add the filter to a page? =
Use the `[sieve]` shortcode or the "Sieve Filter" block. Both render the facets, the results grid, sorting, active-filter chips and pagination together.

= How do I add the predictive search box? =
Use the `[sieve_search]` shortcode or the "Sieve Search" block. As shoppers type, a dropdown shows matching products with thumbnails and prices; it is fully keyboard accessible and falls back to the standard product search when JavaScript is unavailable.

== Screenshots ==

1. Faceted filtering on a product page: categories, price range, availability and on-sale facets with live dependent counts, active-filter chips and a results grid.
2. Mobile filter drawer with a sticky "Show results" bar.
3. The facet builder: add, reorder and retype facets, set the layout, and rebuild the index.

== Changelog ==

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

= 0.1.0 =
* Initial MVP release: pre-built index, AJAX filtering with URL state, dependent facet counts, checkboxes / radio / dropdown / range / search facets, sorting, active-filter chips, pagination, mobile filter drawer, React facet builder, `[sieve]` shortcode and "Sieve Filter" block.
