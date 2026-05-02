<?php
/* Template Name: Meraki Home */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-home-hero">
    <div class="mr-home-hero__content mr-container">
        <span class="mr-kicker"><?php esc_html_e('Meraki Roots CBD', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('From Morning to Night', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Premium CBD products for every part of your routine, backed by third-party lab results.', 'meraki-roots'); ?></p>
        <div class="mr-hero-actions">
            <a class="mr-button" href="<?php echo esc_url(home_url('/shop/')); ?>"><?php esc_html_e('Shop Products', 'meraki-roots'); ?></a>
            <a class="mr-button mr-button--secondary" href="<?php echo esc_url(home_url('/lab-results/')); ?>"><?php esc_html_e('View Lab Results', 'meraki-roots'); ?></a>
        </div>
    </div>
</section>
<?php get_template_part('template-parts/meraki/category-mosaic'); ?>
<section class="mr-home-trust mr-container">
    <span class="mr-kicker"><?php esc_html_e('Transparency First', 'meraki-roots'); ?></span>
    <h2><?php esc_html_e('Lab-tested CBD with clear product information.', 'meraki-roots'); ?></h2>
    <p><?php esc_html_e('Every CBD product page is designed to show product format, ingredients, suggested use, warnings, and available third-party COAs before purchase.', 'meraki-roots'); ?></p>
</section>
<section class="mr-featured-products mr-container-wide">
    <div class="mr-section-heading">
        <span class="mr-kicker"><?php esc_html_e('Featured', 'meraki-roots'); ?></span>
        <h2><?php esc_html_e('Shop Meraki Roots', 'meraki-roots'); ?></h2>
    </div>
    <?php echo do_shortcode('[products limit="8" columns="4" orderby="menu_order" visibility="visible"]'); ?>
</section>
<?php
while (have_posts()) :
    the_post();
    if (trim(get_the_content()) !== '') : ?>
        <section class="mr-page-content mr-container"><?php the_content(); ?></section>
    <?php endif;
endwhile;
get_footer();
