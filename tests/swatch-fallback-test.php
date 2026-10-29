<?php

declare(strict_types=1);

/**
 * Tests for Swatch cross-plugin metadata fallbacks in FacetRenderer.
 *
 *   php tests/swatch-fallback-test.php
 */

define('ABSPATH', __DIR__);

if (! function_exists('__')) {
    function __($text, $domain = null) { return $text; }
}
if (! function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
}
if (! function_exists('esc_attr')) {
    function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
}
if (! function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) { return $value; }
}

if (! class_exists('WP_Term')) {
    class WP_Term
    {
        public int $term_id;
        public string $taxonomy;
        public string $slug;

        public function __construct(int $id, string $taxonomy, string $slug)
        {
            $this->term_id  = $id;
            $this->taxonomy = $taxonomy;
            $this->slug     = $slug;
        }
    }
}

$terms = [];
$termMeta = [];

function get_term_by(string $field, string $value, string $taxonomy) {
    global $terms;
    return $terms[$taxonomy][$value] ?? false;
}

function get_term_meta(int $termId, string $key, bool $single = false) {
    global $termMeta;
    return $termMeta[$termId][$key] ?? ($single ? '' : []);
}

function wp_get_attachment_image_url(int $id, string $size = 'thumbnail') {
    return 'https://example.com/images/' . $id . '.jpg';
}

function absint($val) {
    return abs((int) $val);
}

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Sieve\\')) {
        require_once dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 6)) . '.php';
    }
});

use Sieve\Service\FacetRenderer;

$fail = 0;
function check(bool $ok, string $what): void
{
    global $fail;
    echo ($ok ? 'ok   ' : 'FAIL ') . $what . "\n";
    if (! $ok) {
        $fail++;
    }
}

$renderer = new FacetRenderer();
$colorMethod = new ReflectionMethod($renderer, 'swatchColor');
$colorMethod->setAccessible(true);

$imageMethod = new ReflectionMethod($renderer, 'swatchImage');
$imageMethod->setAccessible(true);

$terms['pa_color']['red']   = new WP_Term(101, 'pa_color', 'red');
$terms['pa_color']['blue']  = new WP_Term(102, 'pa_color', 'blue');
$terms['pa_color']['green'] = new WP_Term(103, 'pa_color', 'green');

// Term 1: Sieve native meta set
$termMeta[101] = [
    'sieve_swatch_color' => '#ff0000',
    'swatch_color'       => '#00ff00',
    'sieve_swatch_image' => 55,
    'swatch_image_id'    => 99,
];

// Term 2: Only Swatch plugin meta set
$termMeta[102] = [
    'sieve_swatch_color' => '',
    'swatch_color'       => '#0000ff',
    'sieve_swatch_image' => '',
    'swatch_image_id'    => 77,
];

// Term 3: Neither set
$termMeta[103] = [];

check('#ff0000' === $colorMethod->invoke($renderer, 'pa_color', 'red'), 'Term red uses native sieve_swatch_color');
check('https://example.com/images/55.jpg' === $imageMethod->invoke($renderer, 'pa_color', 'red'), 'Term red uses native sieve_swatch_image');

check('#0000ff' === $colorMethod->invoke($renderer, 'pa_color', 'blue'), 'Term blue falls back to swatch_color from Swatch');
check('https://example.com/images/77.jpg' === $imageMethod->invoke($renderer, 'pa_color', 'blue'), 'Term blue falls back to swatch_image_id from Swatch');

check('' === $colorMethod->invoke($renderer, 'pa_color', 'green'), 'Term green has empty color');
check('' === $imageMethod->invoke($renderer, 'pa_color', 'green'), 'Term green has empty image');

if ($fail > 0) {
    echo "\n{$fail} checks failed!\n";
    exit(1);
}

echo "\nAll Swatch fallback checks passed.\n";
