<?php

namespace MerakiCommerceCore\Domain\ProductMeta;

final class ProductMetaSchema {
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array {
        return [
            '_mr_current_coa_id'       => self::integer_meta_definition(),
            '_mr_product_form'         => self::text_meta_definition(),
            '_mr_strength_mg'          => self::text_meta_definition(),
            '_mr_size'                 => self::text_meta_definition(),
            '_mr_flavor_scent'         => self::text_meta_definition(),
            '_mr_cbd_type'             => self::text_meta_definition(),
            '_mr_thc_status'           => self::text_meta_definition(),
            '_mr_serving_size'         => self::text_meta_definition(),
            '_mr_servings_per_container' => self::text_meta_definition(),
            '_mr_ingredients'          => self::textarea_meta_definition(),
            '_mr_suggested_use'        => self::textarea_meta_definition(),
            '_mr_warning'              => self::textarea_meta_definition(),
            '_mr_trust_badges'         => self::text_meta_definition(),
            '_mr_category_accent'      => self::text_meta_definition(),
            '_mr_coa_file'             => [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => [ self::class, 'sanitize_url' ],
            ],
            '_mr_coa_batch_id'         => self::text_meta_definition(),
            '_mr_coa_test_date'        => self::text_meta_definition(),
            '_mr_coa_lab_name'         => self::text_meta_definition(),
            '_mr_total_cbd'            => self::text_meta_definition(),
            '_mr_total_thc_status'     => self::text_meta_definition(),
            '_mr_delta9_thc_status'    => self::text_meta_definition(),
            '_mr_coa_category'         => self::text_meta_definition(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function text_meta_definition(): array {
        return [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => [ self::class, 'sanitize_text' ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function textarea_meta_definition(): array {
        return [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => [ self::class, 'sanitize_textarea' ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function integer_meta_definition(): array {
        return [
            'type'              => 'integer',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => [ self::class, 'sanitize_int' ],
        ];
    }

    public static function sanitize_text( mixed $value ): string {
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
    }

    public static function sanitize_textarea( mixed $value ): string {
        return is_scalar( $value ) ? sanitize_textarea_field( (string) $value ) : '';
    }

    public static function sanitize_int( mixed $value ): int {
        return max( 0, (int) $value );
    }

    public static function sanitize_url( mixed $value ): string {
        return is_scalar( $value ) ? esc_url_raw( (string) $value ) : '';
    }
}