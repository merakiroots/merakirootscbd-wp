<?php

namespace MerakiCommerceCore\Domain\COA;

final class CoaRepository {
    public function find_by_legacy_url( string $legacy_url ): int {
        $legacy_url = trim( $legacy_url );
        if ( '' === $legacy_url ) {
            return 0;
        }

        $posts = get_posts(
            [
                'post_type'      => 'mr_coa',
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_mr_legacy_coa_url',
                'meta_value'     => $legacy_url,
            ]
        );

        return is_array( $posts ) && ! empty( $posts ) ? (int) $posts[0] : 0;
    }

    public function append_related_product( int $coa_id, int $product_id ): void {
        $existing = get_post_meta( $coa_id, '_mr_coa_related_product_ids', true );
        $ids      = CoaNormalizer::normalize_product_ids( $existing );

        if ( $product_id > 0 && ! in_array( $product_id, $ids, true ) ) {
            $ids[] = $product_id;
            sort( $ids );
        }

        update_post_meta( $coa_id, '_mr_coa_related_product_ids', $ids );
    }
}