<?php
/**
 * Plugin Name: Meraki Commerce Core
 * Description: Product trust, COA, and compliance data layer for the Meraki Roots relaunch.
 * Version: 0.1.0
 * Author: Meraki Roots
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: meraki-commerce-core
 *
 * @package MerakiCommerceCore
 */

defined( 'ABSPATH' ) || exit;

define( 'MCC_PLUGIN_FILE', __FILE__ );
define( 'MCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MCC_VERSION', '0.1.0' );

$mcc_autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $mcc_autoload ) ) {
	require_once $mcc_autoload;
} else {
	require_once __DIR__ . '/src/Autoloader.php';
	\MerakiCommerceCore\Autoloader::register();
}

add_action(
	'plugins_loaded',
	static function (): void {
		if ( class_exists( \MerakiCommerceCore\Plugin::class ) ) {
			\MerakiCommerceCore\Plugin::boot();
		}
	}
);
