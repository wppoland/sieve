<?php
/**
 * PRO upsell content for the Sieve admin screen. Curated to match the real
 * Sieve PRO edition, do not invent features.
 *
 * @package sieve
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Sieve PRO',
    'url'        => 'https://plogins.com/sieve-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'price_pln'  => 129,
    'features'   => [
        [
            'en' => ['title' => 'Performance dashboard', 'desc' => 'Index health, catalogue coverage, table size and a filter-resolve benchmark under Sieve → Performance.'],
            'pl' => ['title' => 'Panel wydajności', 'desc' => 'Kondycja indeksu, pokrycie katalogu, rozmiar tabeli i benchmark rozwiązywania filtrów w Sieve → Wydajność.'],
        ],
        [
            'en' => ['title' => 'Conditional facet rules', 'desc' => 'Show or hide specific facets by product category, customer role or the shop page.'],
            'pl' => ['title' => 'Warunkowe reguły filtrów', 'desc' => 'Pokazuj lub ukrywaj wybrane filtry według kategorii produktu, roli klienta lub strony sklepu.'],
        ],
        [
            'en' => ['title' => 'A/B layout testing', 'desc' => 'Rotate filter-panel layouts and product grid columns with impression tracking.'],
            'pl' => ['title' => 'Testy A/B układu', 'desc' => 'Rotuj układy panelu filtrów i liczbę kolumn siatki produktów ze śledzeniem wyświetleń.'],
        ],
        [
            'en' => ['title' => 'Star rating facet', 'desc' => 'Visual star rows for the average-rating filter instead of plain checkboxes.'],
            'pl' => ['title' => 'Filtr gwiazdkowy', 'desc' => 'Wizualne rzędy gwiazdek dla filtra średniej oceny zamiast zwykłych pól wyboru.'],
        ],
        [
            'en' => ['title' => 'SearchWP and Algolia', 'desc' => 'Route predictive and in-grid search through SearchWP or Algolia.'],
            'pl' => ['title' => 'SearchWP i Algolia', 'desc' => 'Kieruj wyszukiwanie predykcyjne i w siatce przez SearchWP lub Algolia.'],
        ],
    ],
];
