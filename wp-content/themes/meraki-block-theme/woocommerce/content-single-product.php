<?php
/**
 * Single product content template.
 *
 * @package MerakiBlockTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$product_id = $product ? $product->get_id() : get_the_ID();
$context    = function_exists( 'mcc_get_product_context' ) ? mcc_get_product_context( $product_id ) : array();
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'meraki-single-product', $product ); ?>>
	<?php do_action( 'woocommerce_before_single_product' ); ?>

	<div class="meraki-single-product__grid">
		<div class="meraki-single-product__gallery">
			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
		</div>

		<div class="meraki-single-product__summary">
			<?php if ( ! empty( $context['form'] ) || ! empty( $context['strength'] ) || ! empty( $context['size'] ) ) : ?>
				<div class="meraki-product-kicker">
					<?php echo esc_html( trim( implode( ' • ', array_filter( array( $context['form'] ?? '', $context['strength'] ?? '', $context['size'] ?? '' ) ) ) ) ); ?>
				</div>
			<?php endif; ?>

			<h1 class="product_title entry-title"><?php the_title(); ?></h1>
			<div class="meraki-single-product__price"><?php woocommerce_template_single_price(); ?></div>

			<?php if ( ! empty( $context['overview'] ) ) : ?>
				<div class="meraki-single-product__overview"><?php echo wp_kses_post( wpautop( $context['overview'] ) ); ?></div>
			<?php elseif ( $product && $product->get_short_description() ) : ?>
				<div class="meraki-single-product__overview"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<?php if ( function_exists( 'mcc_render_product_trust_badges' ) ) : ?>
				<?php echo wp_kses_post( mcc_render_product_trust_badges( $product_id ) ); ?>
			<?php endif; ?>

			<div class="meraki-single-product__cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>

			<?php if ( function_exists( 'mcc_render_product_accordions' ) ) : ?>
				<?php echo wp_kses_post( mcc_render_product_accordions( $product_id ) ); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
</article>
