<?php
/**
 * Fallback template.
 */
defined('ABSPATH') || exit;
get_header();
?>
<section class="mr-page-content mr-container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('mr-post'); ?>>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <h1><?php esc_html_e('Nothing found', 'meraki-roots'); ?></h1>
        <p><?php esc_html_e('Try searching or return to the shop.', 'meraki-roots'); ?></p>
    <?php endif; ?>
</section>
<?php get_footer();
