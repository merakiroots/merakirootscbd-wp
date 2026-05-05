<?php
/**
 * Theme setup for Meraki Block Theme.
 *
 * @package MerakiBlockTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers supports and pattern categories.
 *
 * @return void
 */
function meraki_block_theme_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'post-thumbnails' );

	register_block_pattern_category(
		'meraki-home',
		array(
			'label' => __( 'Meraki Home', 'meraki-block-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'meraki_block_theme_setup' );

/**
 * Registers widget areas used by the first archive pass.
 *
 * @return void
 */
function meraki_block_theme_register_sidebars() {
	register_sidebar(
		array(
			'name'          => __( 'Shop Sidebar', 'meraki-block-theme' ),
			'id'            => 'shop-sidebar',
			'description'   => __( 'Optional sidebar used on WooCommerce archive screens.', 'meraki-block-theme' ),
			'before_widget' => '<section class="widget meraki-sidebar-widget">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'meraki_block_theme_register_sidebars' );

/**
 * Enqueues frontend assets.
 *
 * @return void
 */
function meraki_block_theme_enqueue_assets() {
	wp_enqueue_style(
		'meraki-block-theme',
		get_theme_file_uri( '/assets/css/theme.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'meraki_block_theme_enqueue_assets' );

/**
 * Enqueues the shared editor stylesheet.
 *
 * @return void
 */
function meraki_block_theme_enqueue_editor_assets() {
	add_editor_style( 'assets/css/theme.css' );
}
add_action( 'admin_init', 'meraki_block_theme_enqueue_editor_assets' );
