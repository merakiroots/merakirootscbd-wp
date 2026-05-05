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
		'title'       => 'Tinctures',
		'url'         => '/product-category/tinctures/',
		'description' => 'Daily support oils with a clean, direct shopping path.',
		'class_name'  => 'meraki-category-card--sage',
	),
	array(
		'title'       => 'Capsules',
		'url'         => '/product-category/capsules/',
		'description' => 'Simple routines, measured servings, and a calmer product shelf.',
		'class_name'  => 'meraki-category-card--muted',
	),
	array(
		'title'       => 'Vape Cartridges',
		'url'         => '/product-category/vape-cartridges/',
		'description' => 'Fast product discovery for shoppers who already know the format.',
		'class_name'  => 'meraki-category-card--ink',
	),
	array(
		'title'       => 'Terpsolate Diamonds',
		'url'         => '/product-category/terpsolate-diamonds/',
		'description' => 'A focused presentation for higher-intent specialty browsing.',
		'class_name'  => 'meraki-category-card--border',
	),
	array(
		'title'       => 'Topicals',
		'url'         => '/product-category/topicals/',
		'description' => 'Everyday relief products framed with room for trust copy.',
		'class_name'  => 'meraki-category-card--body',
	),
);
?>
<!-- wp:group {"tagName":"section","className":"meraki-category-mosaic","layout":{"type":"constrained"}} -->
<section class="wp-block-group meraki-category-mosaic"><!-- wp:columns {"align":"full","className":"meraki-category-mosaic__grid"} -->
<div class="wp-block-columns alignfull meraki-category-mosaic__grid">
<?php foreach ( $cards as $card ) : ?>
	<!-- wp:column -->
	<div class="wp-block-column"><!-- wp:group {"className":"meraki-category-card <?php echo esc_attr( $card['class_name'] ); ?>","style":{"spacing":{"padding":{"top":"28px","right":"28px","bottom":"28px","left":"28px"}},"dimensions":{"minHeight":"280px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"space-between"}} -->
	<div class="wp-block-group meraki-category-card <?php echo esc_attr( $card['class_name'] ); ?>" style="min-height:280px;padding-top:28px;padding-right:28px;padding-bottom:28px;padding-left:28px"><!-- wp:group {"className":"meraki-category-card__content","layout":{"type":"constrained"}} -->
	<div class="wp-block-group meraki-category-card__content"><!-- wp:heading {"level":3,"textColor":"canvas","fontSize":"lg"} -->
	<h3 class="wp-block-heading has-canvas-color has-text-color has-lg-font-size"><?php echo esc_html( $card['title'] ); ?></h3>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"textColor":"canvas","fontSize":"sm"} -->
	<p class="has-canvas-color has-text-color has-sm-font-size"><?php echo esc_html( $card['description'] ); ?></p>
	<!-- /wp:paragraph --></div>
	<!-- /wp:group -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"canvas","textColor":"ink"} -->
	<div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-canvas-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( $card['url'] ); ?>">Shop Now</a></div>
	<!-- /wp:button --></div>
	<!-- /wp:buttons --></div>
	<!-- /wp:group --></div>
	<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
