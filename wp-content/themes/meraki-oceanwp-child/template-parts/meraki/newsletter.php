<?php defined('ABSPATH') || exit; ?>
<form class="mr-newsletter-form" action="<?php echo esc_url(home_url('/')); ?>" method="get">
    <label class="screen-reader-text" for="mr-newsletter-email"><?php esc_html_e('Email address', 'meraki-roots'); ?></label>
    <input id="mr-newsletter-email" type="email" name="mr_newsletter_email" placeholder="<?php esc_attr_e('Email address', 'meraki-roots'); ?>">
    <button type="submit"><?php esc_html_e('Sign Up', 'meraki-roots'); ?></button>
</form>
