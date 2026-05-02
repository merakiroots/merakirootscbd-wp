<?php
/**
 * Cleanup behavior for Meraki Commerce Core.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$option_names = [
    'meraki_commerce_core_version',
];

foreach ( $option_names as $option_name ) {
    delete_option( $option_name );
}