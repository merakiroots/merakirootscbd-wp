<?php
/* Template Name: Meraki Lab Results */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero mr-page-hero--lab">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('Transparency', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('Third-Party Lab Results', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Review product-level COAs, cannabinoid information, and lab details before purchase.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-page-content mr-container">
    <?php echo do_shortcode('[meraki_lab_results]'); ?>
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
