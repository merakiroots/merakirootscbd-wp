<?php

namespace MerakiCommerceCore\Domain\COA;

final class CoaMetaRegistrar {
    public function register(): void {
        foreach ( $this->definitions() as $meta_key => $meta_args ) {
            $meta_args['auth_callback'] = [ $this, 'auth_callback' ];
            register_post_meta( 'mr_coa', $meta_key, $meta_args );
        }
    }

    public function auth_callback(): bool {
        return current_user_can( 'edit_posts' );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function definitions(): array {
        return [
            '_mr_coa_attachment_id' => [
                'type'              => 'integer',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_int' ],
            ],
            '_mr_coa_batch_id'      => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
            ],
            '_mr_coa_test_date'     => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_date' ],
            ],
            '_mr_coa_lab_name'      => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
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
                'sanitize_callback' => [ $this, 'sanitize_product_ids' ],
            ],
            '_mr_coa_status'        => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_status' ],
            ],
            '_mr_legacy_coa_url'    => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_url' ],
            ],
            '_mr_total_cbd'         => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
            ],
            '_mr_total_thc_status'  => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
            ],
            '_mr_delta9_thc_status' => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
            ],
            '_mr_coa_category'      => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ $this, 'sanitize_text' ],
            ],
        ];
    }

    public function sanitize_text( mixed $value ): string {
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
    }

    public function sanitize_int( mixed $value ): int {
        return max( 0, (int) $value );
    }

    public function sanitize_url( mixed $value ): string {
        return is_scalar( $value ) ? esc_url_raw( (string) $value ) : '';
    }

    public function sanitize_date( mixed $value ): string {
        return CoaNormalizer::normalize_date( is_scalar( $value ) ? (string) $value : '' );
    }

    public function sanitize_status( mixed $value ): string {
        return CoaNormalizer::normalize_status( is_scalar( $value ) ? (string) $value : '' );
    }

    /**
     * @return array<int>
     */
    public function sanitize_product_ids( mixed $value ): array {
        return CoaNormalizer::normalize_product_ids( $value );
    }
}