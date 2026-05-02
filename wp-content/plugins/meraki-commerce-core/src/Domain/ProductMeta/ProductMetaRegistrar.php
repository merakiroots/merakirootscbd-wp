<?php

namespace MerakiCommerceCore\Domain\ProductMeta;

final class ProductMetaRegistrar {
    public function register(): void {
        foreach ( ProductMetaSchema::definitions() as $meta_key => $meta_args ) {
            $meta_args['auth_callback'] = [ $this, 'auth_callback' ];
            register_post_meta( 'product', $meta_key, $meta_args );
        }
    }

    public function auth_callback(): bool {
        return current_user_can( 'edit_posts' );
    }
}