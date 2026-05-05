<?php
/**
 * Admin and editorial flows for COA management.
 *
 * @package MerakiCommerceCore
 */

namespace MerakiCommerceCore;

defined( 'ABSPATH' ) || exit;

final class Admin {

	/**
	 * Add the COA details metabox.
	 */
	public static function add_coa_meta_box(): void {
		add_meta_box(
			'mcc-coa-details',
			__( 'COA Details', 'meraki-commerce-core' ),
			[ self::class, 'render_coa_meta_box' ],
			Repository::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Render the COA metabox.
	 *
	 * @param \WP_Post $post Current COA post.
	 */
	public static function render_coa_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mcc_save_coa', 'mcc_coa_nonce' );

		$attachment_id = absint( get_post_meta( $post->ID, '_mr_coa_attachment_id', true ) );
		$batch_id      = (string) get_post_meta( $post->ID, '_mr_coa_batch_id', true );
		$test_date     = (string) get_post_meta( $post->ID, '_mr_coa_test_date', true );
		$lab_name      = (string) get_post_meta( $post->ID, '_mr_coa_lab_name', true );
		$status        = (string) get_post_meta( $post->ID, '_mr_coa_status', true );
		$product_ids   = get_post_meta( $post->ID, '_mr_coa_related_product_ids', true );
		$attachment    = $attachment_id ? get_post( $attachment_id ) : null;
		$products      = wc_get_products(
			[
				'limit'   => 100,
				'orderby' => 'title',
				'order'   => 'ASC',
				'status'  => [ 'publish', 'draft', 'private' ],
			]
		);

		if ( ! is_array( $product_ids ) ) {
			$product_ids = [];
		}
		?>
		<div class="mcc-admin-grid">
			<p>
				<label for="mcc_coa_attachment_id"><strong><?php esc_html_e( 'COA Attachment', 'meraki-commerce-core' ); ?></strong></label><br />
				<input class="regular-text" type="number" id="mcc_coa_attachment_id" name="_mr_coa_attachment_id" value="<?php echo esc_attr( $attachment_id ); ?>" />
				<button type="button" class="button mcc-select-attachment" data-target="#mcc_coa_attachment_id"><?php esc_html_e( 'Choose PDF', 'meraki-commerce-core' ); ?></button>
				<?php if ( $attachment ) : ?>
					<span class="description"><?php echo esc_html( $attachment->post_title ); ?></span>
				<?php endif; ?>
			</p>
			<p>
				<label for="mcc_coa_batch_id"><strong><?php esc_html_e( 'Batch ID', 'meraki-commerce-core' ); ?></strong></label><br />
				<input class="regular-text" type="text" id="mcc_coa_batch_id" name="_mr_coa_batch_id" value="<?php echo esc_attr( $batch_id ); ?>" />
			</p>
			<p>
				<label for="mcc_coa_test_date"><strong><?php esc_html_e( 'Test Date', 'meraki-commerce-core' ); ?></strong></label><br />
				<input class="regular-text" type="date" id="mcc_coa_test_date" name="_mr_coa_test_date" value="<?php echo esc_attr( $test_date ); ?>" />
			</p>
			<p>
				<label for="mcc_coa_lab_name"><strong><?php esc_html_e( 'Lab Name', 'meraki-commerce-core' ); ?></strong></label><br />
				<input class="regular-text" type="text" id="mcc_coa_lab_name" name="_mr_coa_lab_name" value="<?php echo esc_attr( $lab_name ); ?>" />
			</p>
			<p>
				<label for="mcc_coa_status"><strong><?php esc_html_e( 'Status', 'meraki-commerce-core' ); ?></strong></label><br />
				<select id="mcc_coa_status" name="_mr_coa_status">
					<option value="current" <?php selected( $status, 'current' ); ?>><?php esc_html_e( 'Current', 'meraki-commerce-core' ); ?></option>
					<option value="archived" <?php selected( $status, 'archived' ); ?>><?php esc_html_e( 'Archived', 'meraki-commerce-core' ); ?></option>
					<option value="superseded" <?php selected( $status, 'superseded' ); ?>><?php esc_html_e( 'Superseded', 'meraki-commerce-core' ); ?></option>
				</select>
			</p>
			<p>
				<label for="mcc_coa_related_product_ids"><strong><?php esc_html_e( 'Related Products', 'meraki-commerce-core' ); ?></strong></label><br />
				<select id="mcc_coa_related_product_ids" name="_mr_coa_related_product_ids[]" multiple size="8" class="widefat">
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_attr( $product->get_id() ); ?>" <?php selected( in_array( $product->get_id(), array_map( 'absint', $product_ids ), true ) ); ?>>
							<?php echo esc_html( $product->get_name() ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * Save COA data.
	 *
	 * @param int $post_id COA post ID.
	 */
	public static function save_coa( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( empty( $_POST['mcc_coa_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcc_coa_nonce'] ) ), 'mcc_save_coa' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, '_mr_coa_attachment_id', absint( $_POST['_mr_coa_attachment_id'] ?? 0 ) );
		update_post_meta( $post_id, '_mr_coa_batch_id', sanitize_text_field( wp_unslash( $_POST['_mr_coa_batch_id'] ?? '' ) ) );
		update_post_meta( $post_id, '_mr_coa_test_date', self::sanitize_date( wp_unslash( $_POST['_mr_coa_test_date'] ?? '' ) ) );
		update_post_meta( $post_id, '_mr_coa_lab_name', sanitize_text_field( wp_unslash( $_POST['_mr_coa_lab_name'] ?? '' ) ) );
		update_post_meta( $post_id, '_mr_coa_status', self::sanitize_status( wp_unslash( $_POST['_mr_coa_status'] ?? 'current' ) ) );
		update_post_meta( $post_id, '_mr_coa_related_product_ids', self::sanitize_product_ids( wp_unslash( $_POST['_mr_coa_related_product_ids'] ?? [] ) ) );
	}

	/**
	 * Render the product-side current COA selector.
	 */
	public static function render_product_coa_panel(): void {
		global $post;

		$current_coa_id = absint( get_post_meta( $post->ID, '_mr_current_coa_id', true ) );

		echo '<div class="options_group">';
		woocommerce_wp_select(
			[
				'id'          => '_mr_current_coa_id',
				'label'       => __( 'Current COA', 'meraki-commerce-core' ),
				'description' => __( 'Choose the active certificate of analysis for this product.', 'meraki-commerce-core' ),
				'desc_tip'    => true,
				'options'     => Repository::get_coa_choices(),
				'value'       => $current_coa_id,
			]
		);
		echo '</div>';
	}

	/**
	 * Save current COA selection for a product.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function save_product_coa_panel( int $product_id ): void {
		if ( ! current_user_can( 'edit_post', $product_id ) ) {
			return;
		}

		$coa_id = absint( wp_unslash( $_POST['_mr_current_coa_id'] ?? 0 ) );

		if ( $coa_id && ! Repository::get_coa( $coa_id ) ) {
			return;
		}

		update_post_meta( $product_id, '_mr_current_coa_id', $coa_id );

		if ( $coa_id ) {
			$product_ids = self::sanitize_product_ids( get_post_meta( $coa_id, '_mr_coa_related_product_ids', true ) );
			if ( ! in_array( $product_id, $product_ids, true ) ) {
				$product_ids[] = $product_id;
				update_post_meta( $coa_id, '_mr_coa_related_product_ids', array_values( array_unique( array_map( 'absint', $product_ids ) ) ) );
			}
		}
	}

	/**
	 * Sanitize a COA date into Y-m-d.
	 *
	 * @param string $value Raw date value.
	 * @return string
	 */
	public static function sanitize_date( string $value ): string {
		$value = sanitize_text_field( $value );

		if ( '' === $value ) {
			return '';
		}

		$timestamp = strtotime( $value );

		return $timestamp ? gmdate( 'Y-m-d', $timestamp ) : '';
	}

	/**
	 * Sanitize COA status.
	 *
	 * @param string $value Raw status value.
	 * @return string
	 */
	public static function sanitize_status( string $value ): string {
		$value   = sanitize_key( $value );
		$allowed = [ 'current', 'archived', 'superseded' ];

		return in_array( $value, $allowed, true ) ? $value : 'current';
	}

	/**
	 * Sanitize product IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int>
	 */
	public static function sanitize_product_ids( $value ): array {
		if ( ! is_array( $value ) ) {
			$value = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
		}

		return array_values( array_filter( array_map( 'absint', $value ) ) );
	}
}
