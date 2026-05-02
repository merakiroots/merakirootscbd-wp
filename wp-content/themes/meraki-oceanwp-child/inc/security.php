<?php

defined('ABSPATH') || exit;

add_filter('xmlrpc_enabled', '__return_false');

add_action('init', function () {
    remove_action('wp_head', 'wp_generator');
});

add_filter('wp_headers', function (array $headers): array {
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    return $headers;
});
