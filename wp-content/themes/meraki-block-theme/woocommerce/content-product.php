<?php
/**
 * Product loop card template.
 *
 * @package MerakiBlockTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'meraki-product-card', $product ); ?>>
	<a class="meraki-product-card__link" href="<?php the_permalink(); ?>">
		<div class="meraki-product-card__image">
			<?php echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); ?>
		</div>
		<div class="meraki-product-card__content">
			<h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
			<div class="meraki-product-card__price"><?php woocommerce_template_loop_price(); ?></div>
		</div>
	</a>
</li>
