<?php
/* Template Name: Meraki Partner */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('Wholesale', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('Partner with Meraki Roots', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Tell us about your business, product interests, and estimated order needs. We will follow up with next steps.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-page-content mr-container">
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
