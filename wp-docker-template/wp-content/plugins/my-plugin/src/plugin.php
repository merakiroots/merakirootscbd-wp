<?php
/**
 * Main plugin bootstrap class.
 *
 * @package Local\MyPlugin
 */

namespace Local\MyPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
final class Plugin {

    /**
     * Boot the plugin.
     */
    public static function boot(): void {
        add_action( 'init', [ self::class, 'init' ] );
    }

    /**
     * Initialize plugin behavior.
     */
    public static function init(): void {
        error_log( 'Local MyPlugin initialized.' );
    }
}