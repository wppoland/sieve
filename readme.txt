=== Sieve - Faceted Filter for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, accessible faceted product filtering for WooCommerce: AJAX filters, a mobile drawer, and Core Web Vitals by design.

== Description ==

Sieve lets shoppers narrow down products instantly with checkboxes, ranges, search and sort, without a page reload. It is built to be pleasant to use and fast: accessible widgets, a mobile filter drawer, and a rendering approach designed for Core Web Vitals (no layout shift when results update).

* AJAX filtering with no page reload, with shareable, bookmarkable URLs
* Facet types: checkboxes, radio, searchable dropdown, range slider, search, sort, pagination, reset, active-filter chips
* WooCommerce: categories, tags, attributes, price, stock status, on sale
* A pre-built index for fast filtered queries on large catalogs
* Mobile filter drawer with a sticky Apply bar
* Accessible widgets (keyboard and screen-reader friendly)
* Gutenberg block and shortcode for placement

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

== Changelog ==

= 0.1.0 =
* Initial MVP release: pre-built index, AJAX filtering with URL state, dependent facet counts, checkboxes / radio / dropdown / range / search facets, sorting, active-filter chips, pagination, mobile filter drawer, React facet builder, `[sieve]` shortcode and "Sieve Filter" block.
