<?php defined('ABSPATH') || exit; ?>
<footer class="mr-footer" role="contentinfo">
    <div class="mr-footer__grid mr-container-wide">
        <section class="mr-footer__brand" aria-label="<?php esc_attr_e('Meraki Roots', 'meraki-roots'); ?>">
            <?php echo mr_get_logo_html('mr-footer__logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <p><?php esc_html_e('Premium CBD products rooted in transparency, clean design, and everyday wellness routines.', 'meraki-roots'); ?></p>
        </section>

        <section class="mr-footer__links" aria-label="<?php esc_attr_e('Footer links', 'meraki-roots'); ?>">
            <h2><?php esc_html_e('Quick Links', 'meraki-roots'); ?></h2>
            <?php
            if (has_nav_menu('meraki_footer_nav')) {
                wp_nav_menu([
                    'theme_location' => 'meraki_footer_nav',
                    'container'      => false,
                    'menu_class'     => 'mr-footer-menu',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ]);
            } else {
                mr_render_fallback_menu([
                    __('Shop', 'meraki-roots')        => home_url('/shop/'),
                    __('Lab Results', 'meraki-roots') => home_url('/lab-results/'),
                    __('FAQs', 'meraki-roots')        => home_url('/faqs/'),
                    __('Contact', 'meraki-roots')     => home_url('/contact/'),
                    __('Partner', 'meraki-roots')     => home_url('/partner/'),
                ]);
            }
            ?>
        </section>

        <section class="mr-footer__newsletter" aria-label="<?php esc_attr_e('Newsletter', 'meraki-roots'); ?>">
            <h2><?php esc_html_e('Stay Rooted', 'meraki-roots'); ?></h2>
            <p><?php esc_html_e('Get product updates, limited offers, lab-result announcements, and wellness education from Meraki Roots.', 'meraki-roots'); ?></p>
            <?php get_template_part('template-parts/meraki/newsletter'); ?>
        </section>

        <section class="mr-footer__disclaimer" aria-label="<?php esc_attr_e('FDA disclaimer', 'meraki-roots'); ?>">
            <h2><?php esc_html_e('FDA Disclaimer', 'meraki-roots'); ?></h2>
            <p><?php esc_html_e('These products have not been evaluated by the Food and Drug Administration. These products are not intended to diagnose, treat, cure, or prevent any disease. Consult your physician before use, especially if you are pregnant, nursing, taking medication, or have a medical condition.', 'meraki-roots'); ?></p>
        </section>
    </div>

    <div class="mr-footer__bottom mr-container-wide">
        <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php esc_html_e('Privacy Policy', 'meraki-roots'); ?></a>
        <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php esc_html_e('Refund Policy', 'meraki-roots'); ?></a>
        <a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php esc_html_e('Shipping Policy', 'meraki-roots'); ?></a>
        <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Service', 'meraki-roots'); ?></a>
    </div>
</footer>
