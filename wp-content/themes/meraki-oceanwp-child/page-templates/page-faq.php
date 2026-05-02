<?php
/* Template Name: Meraki FAQ */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('FAQ', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('Frequently Asked Questions', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Product, shipping, lab result, and ordering information.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-page-content mr-container">
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
