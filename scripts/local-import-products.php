<?php
/**
 * Local fixture import for Meraki migration dry-run.
 *
 * Usage:
 *   wp eval-file /var/www/html/scripts/local-import-products.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active before importing products.' );
}

$csv_path = WP_CONTENT_DIR . '/uploads/wc-imports/meraki_woocommerce_products_import_ready_enriched_content_v2.csv';

if ( ! file_exists( $csv_path ) ) {
	WP_CLI::error( 'CSV not found at ' . $csv_path );
}

$handle = fopen( $csv_path, 'r' );
if ( false === $handle ) {
	WP_CLI::error( 'Unable to open CSV at ' . $csv_path );
}

$header = fgetcsv( $handle );
if ( ! is_array( $header ) || empty( $header ) ) {
	fclose( $handle );
	WP_CLI::error( 'CSV appears empty: ' . $csv_path );
}

$created = 0;
$updated = 0;
$skipped = 0;
$imported_product_ids = [];

while ( ( $row = fgetcsv( $handle ) ) !== false ) {
	if ( count( $row ) !== count( $header ) ) {
		$skipped++;
		continue;
	}

	$data = array_combine( $header, $row );
	if ( ! is_array( $data ) ) {
		$skipped++;
		continue;
	}

	$sku = isset( $data['SKU'] ) ? trim( (string) $data['SKU'] ) : '';
	if ( '' === $sku ) {
		$skipped++;
		continue;
	}

	$product_id = wc_get_product_id_by_sku( $sku );
	$product    = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();

	if ( ! $product instanceof WC_Product ) {
		$skipped++;
		continue;
	}

	$product->set_name( (string) ( $data['Name'] ?? $sku ) );
	$product->set_slug( sanitize_title( (string) ( $data['Slug'] ?? $sku ) ) );
	$product->set_status( strtolower( trim( (string) ( $data['Published'] ?? '1' ) ) ) === '1' ? 'publish' : 'draft' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( (string) ( $data['Short description'] ?? '' ) );
	$product->set_description( (string) ( $data['Description'] ?? '' ) );
	$product->set_regular_price( (string) ( $data['Regular price'] ?? '' ) );
	$product->set_sku( $sku );

	$in_stock = strtolower( trim( (string) ( $data['In stock?'] ?? '1' ) ) );
	$product->set_stock_status( in_array( $in_stock, [ '1', 'yes', 'true', 'instock' ], true ) ? 'instock' : 'outofstock' );

	$product_id = $product->save();
	if ( ! $product_id ) {
		$skipped++;
		continue;
	}

	$imported_product_ids[] = (int) $product_id;
	if ( wc_get_product_id_by_sku( $sku ) === $product_id ) {
		if ( isset( $data['ID'] ) && ! empty( $data['ID'] ) ) {
			$updated++;
		} else {
			$created++;
		}
	}

	foreach ( $data as $key => $value ) {
		if ( ! is_string( $key ) || ! str_starts_with( $key, 'meta:' ) ) {
			continue;
		}

		$meta_key   = substr( $key, 5 );
		$meta_value = is_string( $value ) ? trim( $value ) : '';

		if ( '_mr_coa_file' === $meta_key && '' !== $meta_value && str_starts_with( $meta_value, '/' ) ) {
			$meta_value = home_url( $meta_value );
		}

		update_post_meta( $product_id, $meta_key, $meta_value );
	}
}

fclose( $handle );

$attachments_created  = 0;
$attachments_existing = 0;
$attachments_missing  = 0;

if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

$coa_urls = [];
foreach ( $imported_product_ids as $product_id ) {
	$coa_url = trim( (string) get_post_meta( $product_id, '_mr_coa_file', true ) );
	if ( '' === $coa_url ) {
		continue;
	}
	$coa_urls[ $coa_url ] = true;
}

foreach ( array_keys( $coa_urls ) as $coa_url ) {
	$attachment_id = attachment_url_to_postid( $coa_url );
	if ( $attachment_id > 0 ) {
		$attachments_existing++;
		continue;
	}

	$uploads = wp_upload_dir();
	$baseurl = (string) ( $uploads['baseurl'] ?? '' );
	$basedir = (string) ( $uploads['basedir'] ?? '' );

	if ( '' === $baseurl || '' === $basedir || ! str_starts_with( $coa_url, $baseurl . '/' ) ) {
		$attachments_missing++;
		continue;
	}

	$relative_path = ltrim( substr( $coa_url, strlen( $baseurl ) ), '/' );
	$file_path     = trailingslashit( $basedir ) . $relative_path;

	if ( ! file_exists( $file_path ) ) {
		$attachments_missing++;
		continue;
	}

	$file_type   = wp_check_filetype( basename( $file_path ), null );
	$attachment  = [
		'post_mime_type' => (string) ( $file_type['type'] ?? 'application/pdf' ),
		'post_title'     => sanitize_file_name( pathinfo( $file_path, PATHINFO_FILENAME ) ),
		'post_status'    => 'inherit',
	];
	$attachment_id = wp_insert_attachment( $attachment, $file_path );
	if ( is_wp_error( $attachment_id ) || $attachment_id <= 0 ) {
		$attachments_missing++;
		continue;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
	if ( is_array( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	$attachments_created++;
}

$summary = [
	'products_created'     => $created,
	'products_updated'     => $updated,
	'products_skipped'     => $skipped,
	'imported_product_ids' => count( $imported_product_ids ),
	'attachments_created'  => $attachments_created,
	'attachments_existing' => $attachments_existing,
	'attachments_missing'  => $attachments_missing,
];

WP_CLI::log( wp_json_encode( $summary ) );
WP_CLI::success( 'Local product fixture import complete.' );
