<?php
/**
 * Plugin Name: My Plugin
 * Description: Local development plugin.
 * Version: 0.1.0
 * Author: Local Dev
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: my-plugin
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
    error_log( 'My Plugin loaded.' );
} );