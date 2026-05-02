<?php

namespace MerakiCommerceCore\Domain\Frontend;

use MerakiCommerceCore\Domain\COA\CoaNormalizer;

final class LabResultsQuery {
    private ProductCoaPresenter $presenter;

    public function __construct( ProductCoaPresenter $presenter ) {
        $this->presenter = $presenter;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function get_grouped_records(): array {
        $groups = [];

        $coa_posts = get_posts(
            [
                'post_type'      => 'mr_coa',
                'post_status'    => [ 'publish', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        foreach ( $coa_posts as $coa_post ) {
            $coa_id  = (int) $coa_post->ID;
            $record  = $this->presenter->get_coa_record( $coa_id );
            $url     = $record['url'];

            if ( '' === $url ) {
                continue;
            }

            $related_product_ids = CoaNormalizer::normalize_product_ids( get_post_meta( $coa_id, '_mr_coa_related_product_ids', true ) );
            $primary_product_id  = $this->find_primary_product_id( $related_product_ids );

            $form         = __( 'Products', 'meraki-commerce-core' );
            $product_name = get_the_title( $coa_id );
            $permalink    = '';

            if ( $primary_product_id > 0 ) {
                $product_form = (string) get_post_meta( $primary_product_id, '_mr_product_form', true );
                $form         = '' !== $product_form ? $product_form : $form;
                $product_name = get_the_title( $primary_product_id );
                $permalink    = get_permalink( $primary_product_id );
            }

            if ( ! isset( $groups[ $form ] ) ) {
                $groups[ $form ] = [];
            }

            $groups[ $form ][] = [
                'coa_id'          => $coa_id,
                'product_id'      => $primary_product_id,
                'product_name'    => $product_name,
                'product_url'     => $permalink,
                'url'             => $url,
                'lab_name'        => $record['lab_name'],
                'test_date'       => $record['test_date'],
                'total_cbd'       => $record['total_cbd'],
                'total_thc_status'=> $record['total_thc_status'],
            ];
        }

        ksort( $groups );

        return $groups;
    }

    /**
     * @param array<int> $product_ids
     */
    private function find_primary_product_id( array $product_ids ): int {
        foreach ( $product_ids as $product_id ) {
            if ( 'product' === get_post_type( $product_id ) && 'trash' !== get_post_status( $product_id ) ) {
                return (int) $product_id;
            }
        }

        return 0;
    }
}