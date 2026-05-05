<?php
/**
 * Main plugin runtime.
 *
 * @package MerakiCommerceCore
 */

namespace MerakiCommerceCore;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/**
	 * Boot plugin services.
	 */
	public static function boot(): void {
		add_action( 'init', [ self::class, 'register_post_type' ] );
		add_action( 'init', [ self::class, 'register_meta' ] );
		add_action( 'init', [ self::class, 'register_shortcode_and_block' ], 20 );
		add_action( 'rest_api_init', [ self::class, 'register_rest_fields' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_admin_assets' ] );
		add_action( 'add_meta_boxes', [ Admin::class, 'add_coa_meta_box' ] );
		add_action( 'save_post_' . Repository::POST_TYPE, [ Admin::class, 'save_coa' ] );
		add_action( 'woocommerce_product_options_general_product_data', [ Admin::class, 'render_product_coa_panel' ] );
		add_action( 'woocommerce_process_product_meta', [ Admin::class, 'save_product_coa_panel' ] );
		add_action( 'woocommerce_single_product_summary', [ Frontend::class, 'render_product_callout' ], 25 );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'meraki coa migrate-legacy', [ Cli::class, 'migrate_legacy_coas' ] );
		}
	}

	/**
	 * Register the COA content type.
	 */
	public static function register_post_type(): void {
		register_post_type(
			Repository::POST_TYPE,
			[
				'labels'       => [
					'name'          => __( 'Certificates of Analysis', 'meraki-commerce-core' ),
					'singular_name' => __( 'Certificate of Analysis', 'meraki-commerce-core' ),
					'add_new_item'  => __( 'Add COA', 'meraki-commerce-core' ),
					'edit_item'     => __( 'Edit COA', 'meraki-commerce-core' ),
				],
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'supports'     => [ 'title' ],
				'rewrite'      => false,
				'menu_icon'    => 'dashicons-media-document',
			]
		);
	}

	/**
	 * Register product and COA meta.
	 */
	public static function register_meta(): void {
		$product_fields = [
			'_mr_current_coa_id' => [
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => 0,
			],
			'_mr_product_form' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_ingredients' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_suggested_use' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_warning' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
		];

		foreach ( $product_fields as $meta_key => $args ) {
			register_post_meta( 'product', $meta_key, $args );
		}

		$coa_fields = [
			'_mr_coa_attachment_id' => [
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => 0,
			],
			'_mr_coa_batch_id' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_coa_test_date' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => [ Admin::class, 'sanitize_date' ],
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_coa_lab_name' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => '',
			],
			'_mr_coa_related_product_ids' => [
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'  => 'array',
						'items' => [ 'type' => 'integer' ],
					],
				],
				'sanitize_callback' => [ Admin::class, 'sanitize_product_ids' ],
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => [],
			],
			'_mr_coa_status' => [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => [ Admin::class, 'sanitize_status' ],
				'auth_callback'     => [ self::class, 'can_edit_products' ],
				'default'           => 'current',
			],
		];

		foreach ( $coa_fields as $meta_key => $args ) {
			register_post_meta( Repository::POST_TYPE, $meta_key, $args );
		}
	}

	/**
	 * Register frontend lab-results surfaces.
	 */
	public static function register_shortcode_and_block(): void {
		add_shortcode( 'meraki_lab_results', [ Frontend::class, 'render_lab_results_shortcode' ] );

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'meraki-commerce-core/lab-results',
				[
					'render_callback' => [ Frontend::class, 'render_lab_results_shortcode' ],
				]
			);
		}
	}

	/**
	 * Register product REST payload.
	 */
	public static function register_rest_fields(): void {
		register_rest_field(
			'product',
			'mr_compliance',
			[
				'get_callback' => static function ( array $object ): array {
					return Frontend::get_product_payload( absint( $object['id'] ?? 0 ) );
				},
				'schema'       => [
					'description' => __( 'Meraki compliance payload for the product.', 'meraki-commerce-core' ),
					'type'        => 'object',
					'context'     => [ 'view', 'edit' ],
				],
			]
		);
	}

	/**
	 * Load admin assets on product and COA screens.
	 */
	public static function enqueue_admin_assets( string $hook_suffix ): void {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		if ( Repository::POST_TYPE !== $screen->post_type && 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'mcc-admin', MCC_PLUGIN_URL . 'assets/admin.css', [], MCC_VERSION );
		wp_enqueue_script( 'mcc-admin', MCC_PLUGIN_URL . 'assets/admin.js', [ 'jquery' ], MCC_VERSION, true );
	}

	/**
	 * Restrict editing to users who can edit products.
	 */
	public static function can_edit_products(): bool {
		return current_user_can( 'edit_products' );
	}
}
