# Sieve - Faceted Filter for WooCommerce

Sieve lets shoppers narrow down a WooCommerce catalog instantly — checkboxes, ranges, search and sort — without a page reload. It is built to feel fast and stay accessible, with a mobile filter drawer and a rendering approach designed to avoid layout shift.

## Features

- AJAX filtering with no page reload and shareable, bookmarkable URLs.
- Predictive product search: an instant typeahead with thumbnails, prices, SKU and category matches, fully keyboard navigable.
- A wide range of facet types: checkboxes, radio, searchable dropdown, colour and image swatches, hierarchical categories, A-Z index, range slider, sort, pagination and active-filter chips.
- A pre-built index for fast filtered queries on large catalogs.
- A mobile filter drawer with a sticky "Show results" bar.
- Gutenberg blocks and shortcodes (`[sieve]`, `[sieve_search]`) for placement.

## Installation

1. Install and activate WooCommerce.
2. Upload and activate Sieve.
3. Open the Sieve menu, rebuild the index, and adjust the facet set if needed.
4. Place the filter with the `[sieve]` shortcode or the "Sieve Filter" block.

## Frequently Asked Questions

**Does filtering reload the page?**
No. Filtering happens via AJAX with the URL kept in sync, so results are shareable and back-button safe.

**How do I add the predictive search box?**
Use the `[sieve_search]` shortcode or the "Sieve Search" block. It falls back to the standard product search when JavaScript is unavailable.

---

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
