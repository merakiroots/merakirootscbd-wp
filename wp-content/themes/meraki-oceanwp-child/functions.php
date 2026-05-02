<?php
/**
 * Meraki Roots OceanWP Child Theme.
 */

defined('ABSPATH') || exit;

$meraki_includes = [
    'inc/setup.php',
    'inc/helpers.php',
    'inc/enqueue.php',
    'inc/security.php',
    'inc/product-fields.php',
    'inc/coa-fields.php',
    'inc/shortcodes.php',
    'inc/woocommerce-hooks.php',
];

foreach ($meraki_includes as $meraki_include) {
    $meraki_path = get_stylesheet_directory() . '/' . $meraki_include;
    if (file_exists($meraki_path)) {
        require_once $meraki_path;
    }
}

add_filter( 'body_class', function ( array $classes ): array {
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $classes[] = 'mr-shop-page';
    }

    if ( function_exists( 'is_product_category' ) && is_product_category() ) {
        $classes[] = 'mr-product-category-page';
    }

    if ( function_exists( 'is_product' ) && is_product() ) {
        $classes[] = 'mr-single-product-page';
    }

    return $classes;
} );
