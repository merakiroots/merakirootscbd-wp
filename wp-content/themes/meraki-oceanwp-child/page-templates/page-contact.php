<?php
/* Template Name: Meraki Contact */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('Contact', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('Get in Touch', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Questions about orders, products, lab results, or wholesale? Send us a note.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-page-content mr-container">
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
