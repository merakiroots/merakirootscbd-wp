<?php
/**
 * Shared repository helpers for COA records.
 *
 * @package MerakiCommerceCore
 */

namespace MerakiCommerceCore;

defined( 'ABSPATH' ) || exit;

final class Repository {

	public const POST_TYPE = 'mr_coa';

	/**
	 * Get a COA post by ID.
	 *
	 * @param int $coa_id COA post ID.
	 * @return \WP_Post|null
	 */
	public static function get_coa( int $coa_id ): ?\WP_Post {
		$coa = get_post( absint( $coa_id ) );

		if ( ! $coa || self::POST_TYPE !== $coa->post_type ) {
			return null;
		}

		return $coa;
	}

	/**
	 * Get choices for the product-side COA selector.
	 *
	 * @return array<int, string>
	 */
	public static function get_coa_choices(): array {
		$posts = get_posts(
			[
				'post_type'      => self::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'draft', 'private' ],
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$choices = [ 0 => __( 'Select a COA', 'meraki-commerce-core' ) ];

		foreach ( $posts as $post ) {
			$status    = (string) get_post_meta( $post->ID, '_mr_coa_status', true );
			$test_date = (string) get_post_meta( $post->ID, '_mr_coa_test_date', true );
			$label     = $post->post_title;

			if ( $status ) {
				$label .= ' [' . $status . ']';
			}

			if ( $test_date ) {
				$label .= ' - ' . $test_date;
			}

			$choices[ $post->ID ] = $label;
		}

		return $choices;
	}

	/**
	 * Query normalized lab-results records.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_lab_results(): array {
		$posts = get_posts(
			[
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);

		$results = [];

		foreach ( $posts as $post ) {
			$attachment_id = absint( get_post_meta( $post->ID, '_mr_coa_attachment_id', true ) );
			$results[]     = [
				'id'                  => $post->ID,
				'title'               => $post->post_title,
				'attachment_id'       => $attachment_id,
				'attachment_url'      => $attachment_id ? wp_get_attachment_url( $attachment_id ) : '',
				'batch_id'            => (string) get_post_meta( $post->ID, '_mr_coa_batch_id', true ),
				'test_date'           => (string) get_post_meta( $post->ID, '_mr_coa_test_date', true ),
				'lab_name'            => (string) get_post_meta( $post->ID, '_mr_coa_lab_name', true ),
				'status'              => (string) get_post_meta( $post->ID, '_mr_coa_status', true ),
				'related_product_ids' => get_post_meta( $post->ID, '_mr_coa_related_product_ids', true ),
			];
		}

		return $results;
	}

	/**
	 * Find an existing COA by attachment ID.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return \WP_Post|null
	 */
	public static function find_coa_by_attachment_id( int $attachment_id ): ?\WP_Post {
		$posts = get_posts(
			[
				'post_type'      => self::POST_TYPE,
				'posts_per_page' => 1,
				'post_status'    => [ 'publish', 'draft', 'private' ],
				'meta_key'       => '_mr_coa_attachment_id',
				'meta_value'     => absint( $attachment_id ),
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}
}
