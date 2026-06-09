<?php
/**
 * Seed a few WooCommerce products for the Sieve smoke test. Run via:
 *   wp eval-file wp-content/plugins/sieve/scripts/seed.php
 *
 * @package Sieve
 */

$cats = [];
foreach (['Hoodies', 'T-Shirts'] as $name) {
    $existing = term_exists($name, 'product_cat');
    if ($existing) {
        $cats[$name] = (int) ( is_array($existing) ? $existing['term_id'] : $existing );
        continue;
    }
    $created = wp_insert_term($name, 'product_cat');
    $cats[$name] = is_wp_error($created) ? 0 : (int) $created['term_id'];
}

$make = static function (string $name, string $price, ?string $sale, int $cat, string $stock): int {
    $product = new WC_Product_Simple();
    $product->set_name($name);
    $product->set_status('publish');
    $product->set_regular_price($price);
    if (null !== $sale) {
        $product->set_sale_price($sale);
    }
    $product->set_category_ids([$cat]);
    $product->set_manage_stock(false);
    $product->set_stock_status($stock);
    return $product->save();
};

$make('Blue Hoodie', '49', null, $cats['Hoodies'], 'instock');
$make('Red Hoodie', '59', '39', $cats['Hoodies'], 'instock');
$make('Plain Tee', '19', null, $cats['T-Shirts'], 'outofstock');

echo "seeded products in " . count($cats) . " categories\n";
