<?php
/**
 * Seed a clean demo catalog for screenshots. Clears existing products, then
 * creates a varied set across categories with a spread of price, stock and sale
 * so every facet is populated. Run via:
 *   wp eval-file wp-content/plugins/sieve/scripts/seed-demo.php
 *
 * @package Sieve
 */

// Clear existing products for a clean shot.
$existing = get_posts(['post_type' => 'product', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any']);
foreach ($existing as $id) {
    wp_delete_post((int) $id, true);
}

$cats = [];
foreach (['Hoodies', 'T-Shirts', 'Accessories'] as $name) {
    $existingTerm = term_exists($name, 'product_cat');
    if ($existingTerm) {
        $cats[$name] = (int) ( is_array($existingTerm) ? $existingTerm['term_id'] : $existingTerm );
        continue;
    }
    $created = wp_insert_term($name, 'product_cat');
    $cats[$name] = is_wp_error($created) ? 0 : (int) $created['term_id'];
}

$make = static function (string $name, string $price, ?string $sale, int $cat, string $stock): void {
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
    $product->set_short_description('A great product for filtering demos.');
    $product->save();
};

$make('Classic Pullover Hoodie', '59', '49', $cats['Hoodies'], 'instock');
$make('Zip-Up Hoodie', '69', null, $cats['Hoodies'], 'instock');
$make('Oversized Hoodie', '79', '64', $cats['Hoodies'], 'onbackorder');
$make('Cropped Hoodie', '54', null, $cats['Hoodies'], 'outofstock');
$make('Organic Cotton Tee', '24', null, $cats['T-Shirts'], 'instock');
$make('Graphic Tee', '29', '19', $cats['T-Shirts'], 'instock');
$make('Long-Sleeve Tee', '34', null, $cats['T-Shirts'], 'instock');
$make('Pocket Tee', '27', null, $cats['T-Shirts'], 'outofstock');
$make('Canvas Tote Bag', '19', null, $cats['Accessories'], 'instock');
$make('Beanie', '22', '15', $cats['Accessories'], 'instock');

echo "demo catalog seeded\n";
