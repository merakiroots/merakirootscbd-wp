<?php

namespace MerakiCommerceCore\Domain\COA;

final class CoaAdminMetaBox {
    public function register(): void {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_mr_coa', [ $this, 'save_meta' ] );
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'mr_coa_meta',
            __( 'COA Details', 'meraki-commerce-core' ),
            [ $this, 'render_meta_box' ],
            'mr_coa',
            'normal',
            'default'
        );
    }

    public function render_meta_box( \WP_Post $post ): void {
        wp_nonce_field( 'mr_coa_meta_box', 'mr_coa_meta_box_nonce' );

        $fields = [
            '_mr_coa_attachment_id'      => '',
            '_mr_coa_batch_id'           => '',
            '_mr_coa_test_date'          => '',
            '_mr_coa_lab_name'           => '',
            '_mr_coa_status'             => 'current',
            '_mr_legacy_coa_url'         => '',
            '_mr_coa_related_product_ids' => '',
        ];

        foreach ( $fields as $key => $default ) {
            $value = get_post_meta( $post->ID, $key, true );
            if ( '_mr_coa_related_product_ids' === $key ) {
                $value = implode( ',', CoaNormalizer::normalize_product_ids( $value ) );
            }
            $fields[ $key ] = '' !== (string) $value ? $value : $default;
        }

        ?>
        <p>
            <label for="mr_coa_attachment_id"><strong><?php esc_html_e( 'Attachment ID', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="number" id="mr_coa_attachment_id" name="mr_coa_attachment_id" value="<?php echo esc_attr( (string) $fields['_mr_coa_attachment_id'] ); ?>" class="widefat" min="0">
        </p>
        <p>
            <label for="mr_coa_batch_id"><strong><?php esc_html_e( 'Batch ID', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="text" id="mr_coa_batch_id" name="mr_coa_batch_id" value="<?php echo esc_attr( (string) $fields['_mr_coa_batch_id'] ); ?>" class="widefat">
        </p>
        <p>
            <label for="mr_coa_test_date"><strong><?php esc_html_e( 'Test Date', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="date" id="mr_coa_test_date" name="mr_coa_test_date" value="<?php echo esc_attr( (string) $fields['_mr_coa_test_date'] ); ?>" class="widefat">
        </p>
        <p>
            <label for="mr_coa_lab_name"><strong><?php esc_html_e( 'Lab Name', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="text" id="mr_coa_lab_name" name="mr_coa_lab_name" value="<?php echo esc_attr( (string) $fields['_mr_coa_lab_name'] ); ?>" class="widefat">
        </p>
        <p>
            <label for="mr_coa_status"><strong><?php esc_html_e( 'Status', 'meraki-commerce-core' ); ?></strong></label><br>
            <select id="mr_coa_status" name="mr_coa_status" class="widefat">
                <?php foreach ( [ 'current', 'archived', 'superseded' ] as $status ) : ?>
                    <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $fields['_mr_coa_status'], $status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="mr_legacy_coa_url"><strong><?php esc_html_e( 'Legacy COA URL', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="url" id="mr_legacy_coa_url" name="mr_legacy_coa_url" value="<?php echo esc_attr( (string) $fields['_mr_legacy_coa_url'] ); ?>" class="widefat">
        </p>
        <p>
            <label for="mr_coa_related_product_ids"><strong><?php esc_html_e( 'Related Product IDs', 'meraki-commerce-core' ); ?></strong></label><br>
            <input type="text" id="mr_coa_related_product_ids" name="mr_coa_related_product_ids" value="<?php echo esc_attr( (string) $fields['_mr_coa_related_product_ids'] ); ?>" class="widefat" placeholder="12,34,56">
        </p>
        <?php
    }

    public function save_meta( int $post_id ): void {
        if ( ! isset( $_POST['mr_coa_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mr_coa_meta_box_nonce'] ) ), 'mr_coa_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $attachment_id = isset( $_POST['mr_coa_attachment_id'] ) ? (int) $_POST['mr_coa_attachment_id'] : 0;
        $batch_id      = isset( $_POST['mr_coa_batch_id'] ) ? sanitize_text_field( wp_unslash( $_POST['mr_coa_batch_id'] ) ) : '';
        $test_date     = isset( $_POST['mr_coa_test_date'] ) ? CoaNormalizer::normalize_date( sanitize_text_field( wp_unslash( $_POST['mr_coa_test_date'] ) ) ) : '';
        $lab_name      = isset( $_POST['mr_coa_lab_name'] ) ? sanitize_text_field( wp_unslash( $_POST['mr_coa_lab_name'] ) ) : '';
        $status        = isset( $_POST['mr_coa_status'] ) ? CoaNormalizer::normalize_status( sanitize_text_field( wp_unslash( $_POST['mr_coa_status'] ) ) ) : 'current';
        $legacy_url    = isset( $_POST['mr_legacy_coa_url'] ) ? esc_url_raw( wp_unslash( $_POST['mr_legacy_coa_url'] ) ) : '';
        $related_csv   = isset( $_POST['mr_coa_related_product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['mr_coa_related_product_ids'] ) ) : '';

        update_post_meta( $post_id, '_mr_coa_attachment_id', max( 0, $attachment_id ) );
        update_post_meta( $post_id, '_mr_coa_batch_id', $batch_id );
        update_post_meta( $post_id, '_mr_coa_test_date', $test_date );
        update_post_meta( $post_id, '_mr_coa_lab_name', $lab_name );
        update_post_meta( $post_id, '_mr_coa_status', $status );
        update_post_meta( $post_id, '_mr_legacy_coa_url', $legacy_url );
        update_post_meta( $post_id, '_mr_coa_related_product_ids', CoaNormalizer::parse_product_id_csv( $related_csv ) );
    }
}