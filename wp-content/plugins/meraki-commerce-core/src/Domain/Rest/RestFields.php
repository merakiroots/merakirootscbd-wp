<?php

namespace MerakiCommerceCore\Domain\Rest;

use MerakiCommerceCore\Domain\COA\CoaNormalizer;
use MerakiCommerceCore\Domain\Frontend\ProductCoaPresenter;

final class RestFields {
    private ProductCoaPresenter $presenter;

    public function __construct( ProductCoaPresenter $presenter ) {
        $this->presenter = $presenter;
    }

    public function register(): void {
        register_rest_field(
            'product',
            'mr_current_coa',
            [
                'get_callback' => [ $this, 'get_product_coa_field' ],
                'schema'       => [
                    'type'       => 'object',
                    'context'    => [ 'view', 'edit' ],
                    'properties' => [
                        'coa_id'    => [ 'type' => 'integer' ],
                        'url'       => [ 'type' => 'string' ],
                        'lab_name'  => [ 'type' => 'string' ],
                        'test_date' => [ 'type' => 'string' ],
                    ],
                ],
            ]
        );

        register_rest_field(
            'mr_coa',
            'mr_related_product_ids',
            [
                'get_callback' => static function ( array $prepared, string $field_name = '', $request = null, string $object_type = '' ): array {
                    return CoaNormalizer::normalize_product_ids( get_post_meta( (int) $prepared['id'], '_mr_coa_related_product_ids', true ) );
                },
                'schema'       => [
                    'type'    => 'array',
                    'items'   => [ 'type' => 'integer' ],
                    'context' => [ 'view', 'edit' ],
                ],
            ]
        );
    }

    /**
     * @param array<string, mixed> $prepared
     * @return array<string, mixed>
     */
    public function get_product_coa_field( array $prepared, string $field_name = '', $request = null, string $object_type = '' ): array {
        $product_id = isset( $prepared['id'] ) ? (int) $prepared['id'] : 0;
        return $this->presenter->get_product_context( $product_id );
    }
}
