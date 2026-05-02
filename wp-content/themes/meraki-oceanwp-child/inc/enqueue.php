<?php

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    $theme   = wp_get_theme();
    $version = $theme->get('Version') ?: '1.0.0';

    wp_enqueue_style('meraki-style', get_stylesheet_uri(), ['oceanwp-style'], $version);

    $styles = [
        'meraki-base'        => 'meraki-base.css',
        'meraki-header'      => 'meraki-header.css',
        'meraki-footer'      => 'meraki-footer.css',
        'meraki-woocommerce' => 'meraki-woocommerce.css',
        'meraki-pages'       => 'meraki-pages.css',
        'meraki-responsive'  => 'meraki-responsive.css',
    ];

    $dependency = 'meraki-style';
    foreach ($styles as $handle => $file) {
        wp_enqueue_style(
            $handle,
            get_stylesheet_directory_uri() . '/assets/css/' . $file,
            [$dependency],
            file_exists(get_stylesheet_directory() . '/assets/css/' . $file) ? filemtime(get_stylesheet_directory() . '/assets/css/' . $file) : $version
        );
        $dependency = $handle;
    }

    wp_enqueue_script(
        'meraki-accordions',
        get_stylesheet_directory_uri() . '/assets/js/meraki-accordions.js',
        [],
        file_exists(get_stylesheet_directory() . '/assets/js/meraki-accordions.js') ? filemtime(get_stylesheet_directory() . '/assets/js/meraki-accordions.js') : $version,
        true
    );

    wp_enqueue_script(
        'meraki-mobile-menu',
        get_stylesheet_directory_uri() . '/assets/js/meraki-mobile-menu.js',
        [],
        file_exists(get_stylesheet_directory() . '/assets/js/meraki-mobile-menu.js') ? filemtime(get_stylesheet_directory() . '/assets/js/meraki-mobile-menu.js') : $version,
        true
    );
}, 20);
