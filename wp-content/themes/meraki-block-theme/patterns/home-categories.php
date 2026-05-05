<?php
/**
 * Title: Home Categories
 * Slug: meraki-block-theme/home-categories
 * Categories: meraki-home
 * Inserter: no
 *
 * @package MerakiBlockTheme
 */

$cards = array(
	array(
		'title' => 'Tinctures',
		'url'   => '/product-category/tinctures/',
		'image' => 'https://cdn.shopify.com/s/files/1/0531/7781/1127/collections/Ground_Tinctures.jpg?v=1624258967',
	),
	array(
		'title' => 'Capsules',
		'url'   => '/product-category/capsules/',
		'image' => 'https://cdn.shopify.com/s/files/1/0531/7781/1127/collections/cap-final-1.jpg?v=1624259158',
	),
	array(
		'title' => 'Body Lotion',
		'url'   => '/product-category/body-lotion/',
		'image' => 'https://cdn.shopify.com/s/files/1/0531/7781/1127/products/MR_0005_lotions-1.jpg?v=1622702665',
	),
	array(
		'title' => 'Terpsolate Diamonds',
		'url'   => '/product-category/terpsolate-diamonds/',
		'image' => 'https://cdn.shopify.com/s/files/1/0531/7781/1127/collections/0001-6000421393780934199.png?v=1776757577',
	),
	array(
		'title' => 'Vape Cartridges',
		'url'   => '/product-category/vape-cartridges/',
		'image' => 'https://cdn.shopify.com/s/files/1/0531/7781/1127/products/Banana_Kush_Vape.jpg?v=1628915206',
	),
);
?>
<!-- wp:group {"tagName":"section","className":"meraki-category-mosaic","layout":{"type":"constrained"}} -->
<section class="wp-block-group meraki-category-mosaic"><!-- wp:columns {"align":"full","className":"meraki-category-mosaic__grid"} -->
<div class="wp-block-columns alignfull meraki-category-mosaic__grid">
<?php foreach ( $cards as $card ) : ?>
	<!-- wp:column -->
	<div class="wp-block-column"><!-- wp:cover {"url":"<?php echo esc_url( $card['image'] ); ?>","dimRatio":5,"minHeight":280,"className":"meraki-category-card","layout":{"type":"constrained"}} -->
	<div class="wp-block-cover meraki-category-card" style="min-height:280px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-5 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $card['image'] ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"meraki-category-card__content","layout":{"type":"constrained"}} -->
	<div class="wp-block-group meraki-category-card__content"><!-- wp:heading {"level":3,"textColor":"canvas","fontSize":"lg"} -->
	<h3 class="wp-block-heading has-canvas-color has-text-color has-lg-font-size"><?php echo esc_html( $card['title'] ); ?></h3>
	<!-- /wp:heading --></div>
	<!-- /wp:group -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"canvas","textColor":"ink"} -->
	<div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-canvas-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $card['url'] ); ?>">Shop Now</a></div>
	<!-- /wp:button --></div>
	<!-- /wp:buttons --></div>
	<!-- /wp:group --></div></div>
	<!-- /wp:cover --></div>
	<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
