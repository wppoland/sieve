<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Default plugin options. Read through a Settings service (added with the MVP);
 * kept here so defaults live in one place.
 */
return [
    'filter_mode' => 'ajax',        // ajax | reload
    'mobile_drawer' => true,        // slide-in filter drawer on small screens
    'show_counts' => true,          // result counts per facet choice
    'dependent_counts' => true,     // counts reflect the current selection
    'results_min_height' => true,   // reserve container height (zero CLS)
];
