<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package sieve-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Sieve PRO',
    'url'        => 'https://plogins.com/sieve-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'lead'       => [
        'en' => 'The modules below ship in the current PRO release.',
        'pl' => 'Poniższe moduły są dostępne w bieżącym wydaniu PRO.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Performance dashboard', 'desc' => 'Sieve → Performance: index row count, catalogue coverage, table size estimate, full re-index and filter-resolve benchmark.'],
            'pl' => ['title' => 'Panel wydajności', 'desc' => 'Sieve → Performance: liczba wierszy indeksu, pokrycie katalogu, rozmiar tabeli, przebudowa indeksu i benchmark resolve filtrów.'],
        ],
        [
            'en' => ['title' => 'Conditional rules', 'desc' => 'Sieve → Conditional rules: hide selected facets on category archives, the main shop page or for chosen customer roles.'],
            'pl' => ['title' => 'Reguły warunkowe', 'desc' => 'Sieve → Conditional rules: ukrywaj wybrane filtry na archiwach kategorii, stronie sklepu lub dla wybranych ról klientów.'],
        ],
        [
            'en' => ['title' => 'A/B layout testing', 'desc' => 'Sieve → Layout tests: filter panel layout variants (sidebar, stacked, inline) and grid column counts with impression tracking.'],
            'pl' => ['title' => 'Testy układu A/B', 'desc' => 'Sieve → Layout tests: warianty układu panelu filtrów (sidebar, stacked, inline) i liczba kolumn siatki z liczeniem wyświetleń.'],
        ],
        [
            'en' => ['title' => 'Star rating facet', 'desc' => 'Star rating presentation for the average-rating source, visual star rows instead of plain checkboxes.'],
            'pl' => ['title' => 'Filtr gwiazdkowy', 'desc' => 'Prezentacja Star rating dla źródła Average rating, wiersze gwiazdek zamiast checkboxów.'],
        ],
        [
            'en' => ['title' => 'Search integrations', 'desc' => 'Sieve → Integrations: route search through SearchWP and/or Algolia with optional native Sieve fallback.'],
            'pl' => ['title' => 'Integracje wyszukiwania', 'desc' => 'Sieve → Integrations: routing wyszukiwania przez SearchWP i/lub Algolia z opcjonalnym powrotem do natywnego Sieve.'],
        ],
    ],
];
