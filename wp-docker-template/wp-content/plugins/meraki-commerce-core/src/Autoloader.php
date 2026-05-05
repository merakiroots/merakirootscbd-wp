<?php
/**
 * Lightweight PSR-4 autoloader fallback.
 *
 * @package MerakiCommerceCore
 */

namespace MerakiCommerceCore;

defined( 'ABSPATH' ) || exit;

final class Autoloader {

	/**
	 * Register the autoloader.
	 */
	public static function register(): void {
		spl_autoload_register( [ self::class, 'autoload' ] );
	}

	/**
	 * Load plugin classes from the src directory.
	 *
	 * @param string $class_name Fully qualified class name.
	 */
	public static function autoload( string $class_name ): void {
		$prefix = __NAMESPACE__ . '\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative_class = substr( $class_name, strlen( $prefix ) );
		$relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class );
		$file_path      = MCC_PLUGIN_DIR . 'src/' . $relative_path . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}
