<?php
/**
 * Seed a color attribute (with swatch metas) and a nested category to exercise
 * the swatch and hierarchy facet types. Run via:
 *   wp eval-file wp-content/plugins/sieve/scripts/seed-phase1.php
 *
 * @package Sieve
 */

// 1. Global colour attribute pa_color with terms.
if (function_exists('wc_create_attribute')) {
    $exists = wc_get_attribute_taxonomy_id_by_name('color');
    if (! $exists) {
        wc_create_attribute(['name' => 'Color', 'slug' => 'color', 'type' => 'select']);
    }
}
$taxonomy = 'pa_color';
if (! taxonomy_exists($taxonomy)) {
    register_taxonomy($taxonomy, 'product', ['hierarchical' => false]);
}

$colors = ['Red' => '', 'Blue' => '#1d4ed8', 'Black' => ''];
$colorTerms = [];
foreach ($colors as $name => $hex) {
    $existing = term_exists($name, $taxonomy);
    $id = $existing ? (int) (is_array($existing) ? $existing['term_id'] : $existing)
        : (int) (wp_insert_term($name, $taxonomy)['term_id'] ?? 0);
    $colorTerms[$name] = $id;
    if ('' !== $hex && $id) {
        update_term_meta($id, 'sieve_swatch_color', $hex); // explicit swatch color
    }
}

// 2. Nested category: Hoodies > Zip-Up.
$parent = term_exists('Hoodies', 'product_cat');
$parentId = $parent ? (int) (is_array($parent) ? $parent['term_id'] : $parent) : 0;
$childId = 0;
if ($parentId) {
    $child = term_exists('Zip-Up', 'product_cat');
    $childId = $child ? (int) (is_array($child) ? $child['term_id'] : $child)
        : (int) (wp_insert_term('Zip-Up', 'product_cat', ['parent' => $parentId])['term_id'] ?? 0);
}

// 3. Assign colours + the child category to a few products.
$ids = get_posts(['post_type' => 'product', 'posts_per_page' => 6, 'fields' => 'ids', 'post_status' => 'publish']);
$names = array_values($colorTerms);
foreach ($ids as $i => $pid) {
    wp_set_object_terms((int) $pid, [$names[$i % count($names)]], $taxonomy);
    $product = wc_get_product((int) $pid);
    if ($product) {
        $product->set_attributes([]);
        $attr = new WC_Product_Attribute();
        $attr->set_id(wc_attribute_taxonomy_id_by_name('color'));
        $attr->set_name($taxonomy);
        $attr->set_options([$names[$i % count($names)]]);
        $attr->set_visible(true);
        $product->set_attributes([$attr]);
        $product->save();
    }
}
if ($childId && isset($ids[0])) {
    wp_set_object_terms((int) $ids[0], [$childId], 'product_cat', true);
}

echo "phase1 seeded: colors + nested category\n";
