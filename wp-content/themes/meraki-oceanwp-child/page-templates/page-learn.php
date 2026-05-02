<?php
/* Template Name: Meraki Learn */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-hero">
    <div class="mr-container">
        <span class="mr-kicker"><?php esc_html_e('Learn', 'meraki-roots'); ?></span>
        <h1><?php esc_html_e('CBD Education', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Plain-language guides on product types, lab results, labels, and responsible shopping.', 'meraki-roots'); ?></p>
    </div>
</section>
<section class="mr-learn-grid mr-container">
    <?php
    $cards = [
        ['CBD 101', 'Start with the basics: what CBD is, how product labels work, and what to review before buying.', '/learn/cbd-101/'],
        ['How to Read a COA', 'Understand cannabinoid content, THC status, batch details, and lab result terminology.', '/learn/how-to-read-lab-results/'],
        ['Product Types', 'Compare tinctures, capsules, topicals, vape cartridges, and terpsolate diamonds by format.', '/learn/cbd-product-types/'],
    ];
    foreach ($cards as $card) : ?>
        <article class="mr-learn-card">
            <h2><?php echo esc_html($card[0]); ?></h2>
            <p><?php echo esc_html($card[1]); ?></p>
            <a class="mr-button mr-button--secondary" href="<?php echo esc_url(home_url($card[2])); ?>"><?php esc_html_e('Read More', 'meraki-roots'); ?></a>
        </article>
    <?php endforeach; ?>
</section>
<section class="mr-page-content mr-container">
    <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
</section>
<?php get_footer();
