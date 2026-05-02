<?php
/** @var array $args */
defined('ABSPATH') || exit;

$product = $args['product'] ?? null;
if (!$product instanceof WC_Product) {
    return;
}

$sections = [
    'ingredients' => [
        'title' => __('Ingredients', 'meraki-roots'),
        'body'  => mr_get_product_meta($product, '_mr_ingredients'),
    ],
    'suggested-use' => [
        'title' => __('Suggested Use', 'meraki-roots'),
        'body'  => mr_get_product_meta($product, '_mr_suggested_use'),
    ],
    'warning' => [
        'title' => __('Warning', 'meraki-roots'),
        'body'  => mr_get_product_meta($product, '_mr_warning'),
    ],
];

$coa = mr_get_product_meta($product, '_mr_coa_file');
if ($coa) {
    $sections['lab-results'] = [
        'title' => __('Lab Results', 'meraki-roots'),
        'body'  => '<p><a class="mr-button" href="' . esc_url($coa) . '" target="_blank" rel="noopener">' . esc_html__('View third-party lab results', 'meraki-roots') . '</a></p>',
    ];
}
?>
<section class="mr-product-accordions mr-container" aria-label="<?php esc_attr_e('Product details', 'meraki-roots'); ?>">
    <?php foreach ($sections as $key => $section) :
        if (empty($section['body'])) {
            continue;
        }
        $id = 'mr-product-panel-' . $product->get_id() . '-' . sanitize_html_class($key);
        ?>
        <div class="mr-accordion">
            <button class="mr-accordion__trigger" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($section['title']); ?>
                <span aria-hidden="true">+</span>
            </button>
            <div id="<?php echo esc_attr($id); ?>" class="mr-accordion__panel" hidden>
                <?php echo mr_format_meta_paragraphs($section['body']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
