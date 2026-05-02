<?php

namespace MerakiCommerceCore\Domain\COA;

final class CoaNormalizer {
    public static function normalize_date( string $input ): string {
        $input = trim( $input );
        if ( '' === $input ) {
            return '';
        }

        if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $input ) ) {
            return $input;
        }

        $timestamp = strtotime( $input );
        if ( false === $timestamp ) {
            return '';
        }

        return gmdate( 'Y-m-d', $timestamp );
    }

    public static function normalize_status( string $status ): string {
        $status = strtolower( trim( $status ) );
        $allowed = [ 'current', 'archived', 'superseded' ];

        return in_array( $status, $allowed, true ) ? $status : 'current';
    }

    /**
     * @return array<int>
     */
    public static function normalize_product_ids( mixed $value ): array {
        if ( is_string( $value ) ) {
            return self::parse_product_id_csv( $value );
        }

        if ( ! is_array( $value ) ) {
            return [];
        }

        $ids = [];
        foreach ( $value as $item ) {
            $id = (int) $item;
            if ( $id > 0 ) {
                $ids[] = $id;
            }
        }

        $ids = array_values( array_unique( $ids ) );
        sort( $ids );

        return $ids;
    }

    /**
     * @return array<int>
     */
    public static function parse_product_id_csv( string $csv ): array {
        $pieces = preg_split( '/[\s,]+/', trim( $csv ) );
        if ( ! is_array( $pieces ) ) {
            return [];
        }

        $ids = [];
        foreach ( $pieces as $piece ) {
            if ( '' === $piece ) {
                continue;
            }

            $id = (int) $piece;
            if ( $id > 0 ) {
                $ids[] = $id;
            }
        }

        $ids = array_values( array_unique( $ids ) );
        sort( $ids );

        return $ids;
    }
}