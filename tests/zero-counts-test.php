<?php

declare(strict_types=1);

/**
 * The "Options with no matches" setting and the dual-thumb price slider markup.
 *
 *   php tests/zero-counts-test.php
 *
 * No framework on purpose: plain PHP with the few WordPress functions the
 * renderer calls stubbed.
 */

define('ABSPATH', __DIR__);

function __($text, $domain = null) { return $text; }
function _n($single, $plural, $number, $domain = null) { return 1 === $number ? $single : $plural; }
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
function esc_html__($text, $domain = null) { return esc_html($text); }
function esc_attr__($text, $domain = null) { return esc_attr($text); }
function number_format_i18n($n) { return (string) $n; }
function apply_filters($hook, $value, ...$args) { return $value; }
function wp_unique_id($prefix = '') { static $i = 0; return $prefix . (string) ++$i; }
function sanitize_key($key) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string) $key)); }
function sanitize_text_field($s) { return trim((string) $s); }
function selected($a, $b, $echo = true) { return (string) $a === (string) $b ? ' selected="selected"' : ''; }

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Sieve\\')) {
        require_once dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 6)) . '.php';
    }
});

use Sieve\Model\Facet;
use Sieve\Service\FacetCountService;
use Sieve\Service\FacetRenderer;
use Sieve\Service\Settings;

$fail = 0;
function check(bool $ok, string $what): void
{
    global $fail;
    echo ($ok ? 'ok   ' : 'FAIL ') . $what . "\n";
    if (! $ok) {
        $fail++;
    }
}

// Sanitising: anything but 'show' is the default 'hide'.
check('show' === Settings::zeroCounts('show'), "zeroCounts('show') stays show");
check('hide' === Settings::zeroCounts('hide'), "zeroCounts('hide') stays hide");
check('hide' === Settings::zeroCounts(null), 'zeroCounts(null) falls back to hide');
check('hide' === Settings::zeroCounts('<script>'), 'zeroCounts(junk) falls back to hide');

// Merge: hide mode passes an empty universe, so nothing is added.
$counts = ['red' => 3];
$universe = ['red' => 5, 'blue' => 2, 'green' => 1];
check($counts === FacetCountService::withZeroes($counts, []), 'hide: counts unchanged');
$merged = FacetCountService::withZeroes($counts, $universe);
check(['red' => 3, 'blue' => 0, 'green' => 0] == $merged && 3 === $merged['red'], 'show: missing values added as 0, live counts kept');
$numeric = FacetCountService::withZeroes([5 => 2], [5 => 4, 4 => 1]);
check([5 => 2, 4 => 0] == $numeric, 'show: numeric keys (rating) survive the merge');

$renderer = new FacetRenderer();
$facet = Facet::fromArray(['slug' => 'color', 'label' => 'Color', 'type' => 'checkbox', 'source' => 'stock']);

$html = $renderer->render($facet, ['instock' => 3, 'outofstock' => 0], []);
check(1 === preg_match('/value="outofstock" disabled aria-disabled="true">/', $html), 'zero option: disabled + aria-disabled');
check(1 === preg_match('/sieve-choice is-empty.*outofstock.*sieve-choice__count">0</s', $html), 'zero option: is-empty class and count 0 shown');
check(1 === preg_match('/value="instock">/', $html), 'non-zero option stays enabled');

$html = $renderer->render($facet, ['instock' => 3, 'outofstock' => 0], ['outofstock']);
check(1 === preg_match('/value="outofstock" checked>/', $html), 'selected zero option stays enabled so it can be cleared');

$html = $renderer->render($facet, ['instock' => 3], []);
check(false === strpos($html, 'disabled'), 'hide mode (no zeros): nothing disabled');

$dropdown = Facet::fromArray(['slug' => 'stock', 'label' => 'Stock', 'type' => 'dropdown', 'source' => 'stock']);
$html = $renderer->render($dropdown, ['instock' => 3, 'outofstock' => 0], []);
check(1 === preg_match('/value="outofstock" disabled aria-disabled="true">Out of stock \(0\)/', $html), 'dropdown: zero option disabled with (0)');

// Price slider: two labelled range inputs, whole-unit bounds, fill from the selection.
$price = Facet::fromArray(['slug' => 'price', 'label' => 'Price', 'type' => 'range_slider', 'source' => 'price']);
$html = $renderer->render($price, [], ['20-60'], ['min' => 9.99, 'max' => 99.5]);
check(2 === substr_count($html, 'type="range"'), 'slider: two range inputs');
check(1 === preg_match('/thumb--min" min="9" max="100" step="1" value="20" aria-label="Minimum price"/', $html), 'slider: min thumb floored bounds, value, label');
check(1 === preg_match('/thumb--max" min="9" max="100" step="1" value="60" aria-label="Maximum price"/', $html), 'slider: max thumb value and label');
check(1 === preg_match('/--sieve-lo:12\.09%;--sieve-hi:56\.04%/', $html), 'slider: fill positions rendered server side');
check(1 === preg_match('/class="sieve-range__min"[^>]*min="9" max="100"[^>]*value="20"/', $html), 'number inputs kept, same bounds');
check(1 === preg_match('/name="sf_price" class="sieve-range__value" value="20-60"/', $html), 'hidden URL value kept');

$html = $renderer->render($price, [], [], ['min' => 0.0, 'max' => 0.0]);
check(false === strpos($html, 'type="range"'), 'slider: no track when min == max');

if ($fail > 0) {
    echo "\n{$fail} check(s) failed\n";
    exit(1);
}
echo "\nall checks passed\n";
