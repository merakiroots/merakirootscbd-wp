<?php
/**
 * Single product wrapper template.
 *
 * @package MerakiBlockTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="meraki-shell meraki-shell--wide meraki-single-product-shell">
	<?php do_action( 'woocommerce_before_main_content' ); ?>
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	<?php endwhile; ?>
	<?php do_action( 'woocommerce_after_main_content' ); ?>
</div>
<?php
get_footer();
