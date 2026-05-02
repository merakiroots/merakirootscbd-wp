<?php

namespace MerakiCommerceCore\Support;

final class Autoloader {
    private const PREFIX = 'MerakiCommerceCore\\';

    public static function register(): void {
        spl_autoload_register( [ self::class, 'autoload' ] );
    }

    private static function autoload( string $class_name ): void {
        if ( ! str_starts_with( $class_name, self::PREFIX ) ) {
            return;
        }

        $relative_class = substr( $class_name, strlen( self::PREFIX ) );
        $relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';
        $file_path      = dirname( __DIR__, 2 ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relative_path;

        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}