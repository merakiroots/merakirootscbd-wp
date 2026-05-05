<?php
/**
 * Product archive template.
 *
 * @package MerakiBlockTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="meraki-shop-shell">
	<?php do_action( 'woocommerce_before_main_content' ); ?>
	<header class="meraki-shop-archive-header">
		<div class="meraki-shell meraki-shell--wide">
			<?php the_archive_title( '<h1 class="meraki-shop-archive-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="meraki-shop-archive-description">', '</div>' ); ?>
		</div>
	</header>

	<div class="meraki-shell meraki-shell--wide meraki-shop-layout">
		<aside class="meraki-shop-sidebar">
			<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
				<?php dynamic_sidebar( 'shop-sidebar' ); ?>
			<?php else : ?>
				<div class="meraki-shop-sidebar__fallback">
					<h2><?php esc_html_e( 'Category', 'meraki-block-theme' ); ?></h2>
					<?php
					wp_list_categories(
						array(
							'taxonomy'   => 'product_cat',
							'title_li'   => '',
							'hide_empty' => false,
						)
					);
					?>
				</div>
			<?php endif; ?>
		</aside>

		<section class="meraki-shop-content">
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php do_action( 'woocommerce_before_shop_loop' ); ?>
				<ul class="products columns-3 meraki-product-grid">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
					<?php endwhile; ?>
				</ul>
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>
		</section>
	</div>
	<?php do_action( 'woocommerce_after_main_content' ); ?>
</div>
<?php
get_footer();
