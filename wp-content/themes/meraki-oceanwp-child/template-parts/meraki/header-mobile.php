<?php defined('ABSPATH') || exit; ?>
<header class="mr-mobile-header" role="banner">
    <div class="mr-mobile-header__inner">
        <button class="mr-mobile-toggle" type="button" aria-controls="mr-mobile-menu" aria-expanded="false">
            <span class="screen-reader-text"><?php esc_html_e('Open menu', 'meraki-roots'); ?></span>
            <span class="mr-mobile-toggle__bar"></span>
            <span class="mr-mobile-toggle__bar"></span>
            <span class="mr-mobile-toggle__bar"></span>
        </button>

        <?php echo mr_get_logo_html('mr-mobile-header__logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <a class="mr-mobile-cart" href="<?php echo esc_url(mr_wc_cart_url()); ?>" aria-label="<?php esc_attr_e('Cart', 'meraki-roots'); ?>">
            <?php esc_html_e('Cart', 'meraki-roots'); ?>
        </a>
    </div>

    <nav id="mr-mobile-menu" class="mr-mobile-menu" hidden aria-label="<?php esc_attr_e('Mobile navigation', 'meraki-roots'); ?>">
        <?php
        if (has_nav_menu('meraki_mobile_nav')) {
            wp_nav_menu([
                'theme_location' => 'meraki_mobile_nav',
                'container'      => false,
                'menu_class'     => 'mr-mobile-menu__list',
                'depth'          => 3,
                'fallback_cb'    => false,
            ]);
        } else {
            mr_render_fallback_menu([
                __('Shop', 'meraki-roots')                 => home_url('/shop/'),
                __('Tinctures', 'meraki-roots')            => home_url('/product-category/tinctures/'),
                __('Capsules', 'meraki-roots')             => home_url('/product-category/capsules/'),
                __('Vape Cartridges', 'meraki-roots')      => home_url('/product-category/vape-cartridges/'),
                __('Terpsolate Diamonds', 'meraki-roots')  => home_url('/product-category/terpsolate-diamonds/'),
                __('Topicals', 'meraki-roots')             => home_url('/product-category/topicals/'),
                __('Lab Results', 'meraki-roots')          => home_url('/lab-results/'),
                __('Learn', 'meraki-roots')                => home_url('/learn/'),
                __('Partner', 'meraki-roots')              => home_url('/partner/'),
                __('Contact', 'meraki-roots')              => home_url('/contact/'),
            ]);
        }
        ?>
    </nav>
</header>
