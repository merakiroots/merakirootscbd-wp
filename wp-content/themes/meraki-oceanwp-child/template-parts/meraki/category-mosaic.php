<?php defined('ABSPATH') || exit;
$categories = [
    'tinctures'              => __('Tinctures', 'meraki-roots'),
    'capsules'               => __('Capsules', 'meraki-roots'),
    'vape-cartridges'        => __('Vape Cartridges', 'meraki-roots'),
    'terpsolate-diamonds'    => __('Terpsolate Diamonds', 'meraki-roots'),
    'topicals'               => __('Topicals', 'meraki-roots'),
];
?>
<section class="mr-category-mosaic mr-container-wide">
    <div class="mr-section-heading">
        <span class="mr-kicker"><?php esc_html_e('Shop by Category', 'meraki-roots'); ?></span>
        <h2><?php esc_html_e('CBD for every part of your routine.', 'meraki-roots'); ?></h2>
    </div>
    <div class="mr-category-mosaic__grid">
        <?php foreach ($categories as $slug => $label) :
            $term = get_term_by('slug', $slug, 'product_cat');
            $url  = $term ? get_term_link($term) : home_url('/shop/');
            $thumb_id = $term ? get_term_meta($term->term_id, 'thumbnail_id', true) : 0;
            $img = $thumb_id ? wp_get_attachment_image_url((int) $thumb_id, 'large') : '';
            ?>
            <a class="mr-category-card" href="<?php echo esc_url($url); ?>">
                <?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt=""><?php endif; ?>
                <span><?php echo esc_html($label); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
