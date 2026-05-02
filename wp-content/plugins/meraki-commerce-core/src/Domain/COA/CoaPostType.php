<?php

namespace MerakiCommerceCore\Domain\COA;

final class CoaPostType {
    public function register(): void {
        register_post_type(
            'mr_coa',
            [
                'label'           => __( 'COA Records', 'meraki-commerce-core' ),
                'labels'          => [
                    'name'          => __( 'COA Records', 'meraki-commerce-core' ),
                    'singular_name' => __( 'COA Record', 'meraki-commerce-core' ),
                    'add_new_item'  => __( 'Add COA Record', 'meraki-commerce-core' ),
                    'edit_item'     => __( 'Edit COA Record', 'meraki-commerce-core' ),
                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'show_in_rest'    => true,
                'menu_icon'       => 'dashicons-shield-alt',
                'supports'        => [ 'title' ],
                'capability_type' => 'post',
                'map_meta_cap'    => true,
            ]
        );
    }
}