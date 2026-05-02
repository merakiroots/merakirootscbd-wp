<?php

defined('ABSPATH') || exit;

add_action('after_setup_theme', function () {
    load_child_theme_textdomain('meraki-roots', get_stylesheet_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 180,
        'width'       => 180,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'meraki_left_nav'   => __('Meraki Left Navigation', 'meraki-roots'),
        'meraki_right_nav'  => __('Meraki Right Navigation', 'meraki-roots'),
        'meraki_mobile_nav' => __('Meraki Mobile Navigation', 'meraki-roots'),
        'meraki_footer_nav' => __('Meraki Footer Navigation', 'meraki-roots'),
    ]);
});

add_filter('body_class', function (array $classes): array {
    $classes[] = 'mr-theme';
    return $classes;
});
