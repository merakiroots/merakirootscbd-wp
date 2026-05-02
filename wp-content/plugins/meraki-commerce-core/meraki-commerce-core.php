<?php
/**
 * Plugin Name: Meraki Commerce Core
 * Description: Plugin-owned source of truth for Meraki COA, compliance, and product trust data.
 * Version: 0.1.0
 * Author: Meraki Roots
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: meraki-commerce-core
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/src/Support/Autoloader.php';

\MerakiCommerceCore\Support\Autoloader::register();

$meraki_commerce_core_bootstrap = new \MerakiCommerceCore\Bootstrap();
$meraki_commerce_core_bootstrap->register();