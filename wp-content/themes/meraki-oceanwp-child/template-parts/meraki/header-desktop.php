<?php defined('ABSPATH') || exit; ?>
<header class="mr-header" role="banner">
    <div class="mr-header__inner">
        <nav class="mr-header__nav mr-header__nav-left" aria-label="<?php esc_attr_e('Primary left navigation', 'meraki-roots'); ?>">
            <?php
            if (has_nav_menu('meraki_left_nav')) {
                wp_nav_menu([
                    'theme_location' => 'meraki_left_nav',
                    'container'      => false,
                    'menu_class'     => 'mr-menu',
                    'depth'          => 2,
                    'fallback_cb'    => false,
                ]);
            } else {
                mr_render_fallback_menu([
                    __('Shop', 'meraki-roots')  => home_url('/shop/'),
                    __('Learn', 'meraki-roots') => home_url('/learn/'),
                    __('About', 'meraki-roots') => home_url('/about/'),
                ]);
            }
            ?>
        </nav>

        <?php echo mr_get_logo_html('mr-header__logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <nav class="mr-header__nav mr-header__nav-right" aria-label="<?php esc_attr_e('Primary right navigation', 'meraki-roots'); ?>">
            <?php
            if (has_nav_menu('meraki_right_nav')) {
                wp_nav_menu([
                    'theme_location' => 'meraki_right_nav',
                    'container'      => false,
                    'menu_class'     => 'mr-menu',
                    'depth'          => 2,
                    'fallback_cb'    => false,
                ]);
            } else {
                mr_render_fallback_menu([
                    __('Lab Results', 'meraki-roots') => home_url('/lab-results/'),
                    __('Partner', 'meraki-roots')     => home_url('/partner/'),
                    __('Contact', 'meraki-roots')     => home_url('/contact/'),
                ]);
            }
            ?>
            <ul class="mr-menu mr-menu--utility">
                <li><a href="<?php echo esc_url(mr_wc_my_account_url()); ?>"><?php esc_html_e('Account', 'meraki-roots'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php esc_attr_e('Search', 'meraki-roots'); ?>"><?php esc_html_e('Search', 'meraki-roots'); ?></a></li>
                <li><a href="<?php echo esc_url(mr_wc_cart_url()); ?>" aria-label="<?php esc_attr_e('Cart', 'meraki-roots'); ?>"><?php esc_html_e('Cart', 'meraki-roots'); ?></a></li>
            </ul>
        </nav>
    </div>
</header>
