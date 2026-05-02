<?php
/* Template Name: Meraki About */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('About', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('Rooted in transparency, creativity, and care.', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Meraki Roots creates premium CBD products with clear product information, third-party lab results, and a minimalist design language.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-page-content mr-container">
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
