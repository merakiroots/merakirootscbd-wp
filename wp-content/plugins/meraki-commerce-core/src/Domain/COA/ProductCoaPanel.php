<?php

namespace MerakiCommerceCore\Domain\COA;

use MerakiCommerceCore\Domain\Frontend\ProductCoaPresenter;

final class ProductCoaPanel {
    private ProductCoaPresenter $presenter;

    public function __construct( ProductCoaPresenter $presenter ) {
        $this->presenter = $presenter;
    }

    public function register(): void {
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'render_field' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_field' ] );
    }

    public function render_field(): void {
        if ( ! function_exists( 'woocommerce_wp_select' ) ) {
            return;
        }

        $current_value = (int) get_post_meta( get_the_ID(), '_mr_current_coa_id', true );

        $options = [ 0 => __( 'Select a COA record', 'meraki-commerce-core' ) ];

        $records = get_posts(
            [
                'post_type'      => 'mr_coa',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => 250,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        foreach ( $records as $record ) {
            $options[ $record->ID ] = $record->post_title . ' (#' . $record->ID . ')';
        }

        woocommerce_wp_select(
            [
                'id'          => '_mr_current_coa_id',
                'label'       => __( 'Current COA', 'meraki-commerce-core' ),
                'options'     => $options,
                'value'       => $current_value,
                'description' => __( 'Assign the normalized COA record this product should use.', 'meraki-commerce-core' ),
                'desc_tip'    => true,
            ]
        );
    }

    public function save_field( int $product_id ): void {
        if ( ! current_user_can( 'edit_post', $product_id ) ) {
            return;
        }

        $coa_id = isset( $_POST['_mr_current_coa_id'] ) ? (int) $_POST['_mr_current_coa_id'] : 0;

        if ( $coa_id > 0 && 'mr_coa' === get_post_type( $coa_id ) ) {
            update_post_meta( $product_id, '_mr_current_coa_id', $coa_id );

            $context = $this->presenter->get_coa_record( $coa_id );
            if ( '' !== $context['url'] ) {
                update_post_meta( $product_id, '_mr_coa_file', $context['url'] );
            }
        } else {
            delete_post_meta( $product_id, '_mr_current_coa_id' );
        }
    }
}
