<?php
/**
 * Product-driven lab results list.
 */
defined('ABSPATH') || exit;

if (!class_exists('WooCommerce')) {
    return;
}

$products = wc_get_products([
    'status' => 'publish',
    'limit'  => -1,
    'orderby'=> 'menu_order',
    'order'  => 'ASC',
]);

$groups = [];
foreach ($products as $product) {
    if (!$product instanceof WC_Product || !mr_product_has_coa($product)) {
        continue;
    }

    $form = mr_get_product_meta($product, '_mr_product_form', __('Products', 'meraki-roots'));
    $groups[$form][] = $product;
}
?>
<div class="mr-lab-results-list">
    <?php foreach ($groups as $form => $items) : ?>
        <section class="mr-lab-group">
            <h2><?php echo esc_html($form); ?></h2>
            <div class="mr-lab-group__rows">
                <?php foreach ($items as $product) : ?>
                    <article class="mr-lab-row">
                        <div>
                            <h3><a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
                            <p>
                                <?php echo esc_html(mr_get_product_meta($product, '_mr_coa_lab_name', 'Third-party lab')); ?>
                                <?php if (mr_get_product_meta($product, '_mr_coa_test_date')) : ?>
                                    · <?php echo esc_html(mr_get_product_meta($product, '_mr_coa_test_date')); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <a class="mr-button" href="<?php echo esc_url(mr_get_product_meta($product, '_mr_coa_file')); ?>" target="_blank" rel="noopener"><?php esc_html_e('View COA', 'meraki-roots'); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
