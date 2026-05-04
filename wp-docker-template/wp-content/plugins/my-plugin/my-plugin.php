<?php
/**
 * Plugin Name: My Plugin
 * Description: Local WordPress/WooCommerce plugin.
 * Version: 0.1.0
 * Author: Local Dev
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: my-plugin
 *
 * @package Local\MyPlugin
 */

defined( 'ABSPATH' ) || exit;

$my_plugin_autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $my_plugin_autoload ) ) {
    require_once $my_plugin_autoload;
}

add_action(
    'plugins_loaded',
    static function (): void {
        if ( class_exists( \Local\MyPlugin\Plugin::class ) ) {
            \Local\MyPlugin\Plugin::boot();
        }
    }
);