<?php
/** @var array $args */
defined('ABSPATH') || exit;

$term = $args['term'] ?? null;
if (!$term || empty($term->term_id)) {
    return;
}

$thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
$image_url    = $thumbnail_id ? wp_get_attachment_image_url((int) $thumbnail_id, 'full') : '';
$description  = term_description($term, 'product_cat');
?>
<section class="mr-collection-hero <?php echo $image_url ? 'mr-collection-hero--image' : 'mr-collection-hero--plain'; ?>">
    <?php if ($image_url) : ?>
        <img class="mr-collection-hero__image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
    <?php endif; ?>
    <div class="mr-collection-hero__content mr-container">
        <span class="mr-kicker"><?php esc_html_e('Shop Meraki Roots', 'meraki-roots'); ?></span>
        <h1><?php echo esc_html($term->name); ?></h1>
        <?php if ($description) : ?><div class="mr-collection-hero__description"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
    </div>
</section>
