<?php

require_once __DIR__ . '/../src/Domain/COA/CoaNormalizer.php';

use MerakiCommerceCore\Domain\COA\CoaNormalizer;

function assert_same( mixed $expected, mixed $actual, string $label ): void {
    if ( $expected !== $actual ) {
        fwrite( STDERR, "FAIL: {$label}\nExpected: " . var_export( $expected, true ) . "\nActual:   " . var_export( $actual, true ) . "\n" );
        exit( 1 );
    }

    echo "PASS: {$label}\n";
}

assert_same( '2026-04-30', CoaNormalizer::normalize_date( '2026-04-30' ), 'normalize_date preserves Y-m-d input' );
assert_same( '2026-04-30', CoaNormalizer::normalize_date( '04/30/2026' ), 'normalize_date parses slash date' );
assert_same( '', CoaNormalizer::normalize_date( 'not-a-date' ), 'normalize_date clears invalid dates' );

assert_same( 'current', CoaNormalizer::normalize_status( 'CURRENT' ), 'normalize_status lowercases valid values' );
assert_same( 'archived', CoaNormalizer::normalize_status( 'archived' ), 'normalize_status keeps archived' );
assert_same( 'current', CoaNormalizer::normalize_status( 'unknown' ), 'normalize_status defaults unknown values' );

assert_same( [ 1, 2, 3 ], CoaNormalizer::parse_product_id_csv( '3,2,1,2' ), 'parse_product_id_csv dedupes and sorts' );
assert_same( [ 12, 90 ], CoaNormalizer::normalize_product_ids( [ '90', 12, 12, 0, -5 ] ), 'normalize_product_ids coerces array values' );
assert_same( [ 7, 8, 9 ], CoaNormalizer::normalize_product_ids( '9,8,7,7' ), 'normalize_product_ids accepts csv string' );

echo "All tests passed.\n";