<?php

defined('ABSPATH') || exit;

add_action('woocommerce_before_main_content', 'mr_render_collection_hero', 5);
add_action('woocommerce_after_shop_loop_item_title', 'mr_render_product_card_meta', 7);
add_action('woocommerce_after_shop_loop_item_title', 'mr_render_product_card_trust_line', 8);

remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
add_action('woocommerce_after_shop_loop_item', 'mr_render_product_card_cta', 10);

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
add_action('woocommerce_single_product_summary', 'mr_render_product_trust_icons', 25);
add_action('woocommerce_single_product_summary', 'mr_render_product_coa_callout', 31);

remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
add_action('woocommerce_after_single_product_summary', 'mr_render_product_accordions', 9);

add_action('woocommerce_before_cart_totals', 'mr_render_free_shipping_message');
add_action('woocommerce_review_order_before_payment', 'mr_render_checkout_disclaimer');

add_filter('woocommerce_product_tabs', 'mr_custom_product_tabs');

function mr_render_collection_hero(): void {
    if (!is_product_category()) {
        return;
    }

    $term = get_queried_object();
    if (!$term || empty($term->term_id)) {
        return;
    }

    get_template_part('template-parts/meraki/collection-hero', null, ['term' => $term]);
}

function mr_render_product_card_meta(): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    $parts = array_filter([
        mr_get_product_meta($product, '_mr_strength_mg'),
        mr_get_product_meta($product, '_mr_size'),
        mr_get_product_meta($product, '_mr_product_form'),
    ]);

    if ($parts) {
        echo '<div class="mr-product-card__meta">' . esc_html(implode(' · ', $parts)) . '</div>';
    }
}

function mr_render_product_card_trust_line(): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    $form = mr_get_product_meta($product, '_mr_product_form');
    if (strtolower($form) === 'apparel') {
        return;
    }

    $thc_status = mr_get_product_meta($product, '_mr_thc_status', 'Lab-tested');
    echo '<div class="mr-product-card__trust">' . esc_html($thc_status) . ' · ' . esc_html__('Lab-tested', 'meraki-roots') . '</div>';
}

function mr_render_product_card_cta(): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    echo '<a class="mr-product-card__cta" href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html__('View Product', 'meraki-roots') . '</a>';
}

function mr_render_product_trust_icons(): void {
    global $product;
    if ($product instanceof WC_Product && strtolower(mr_get_product_meta($product, '_mr_product_form')) === 'apparel') {
        return;
    }
    get_template_part('template-parts/meraki/trust-icons');
}

function mr_render_product_coa_callout(): void {
    global $product;
    if (!$product instanceof WC_Product || !mr_product_has_coa($product)) {
        return;
    }

    get_template_part('template-parts/meraki/product-coa-callout', null, [
        'product'  => $product,
        'coa_file' => mr_get_product_meta($product, '_mr_coa_file'),
    ]);
}

function mr_render_product_accordions(): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    get_template_part('template-parts/meraki/product-accordion', null, ['product' => $product]);
}

function mr_custom_product_tabs(array $tabs): array {
    unset($tabs['additional_information']);

    $tabs['ingredients'] = [
        'title'    => __('Ingredients', 'meraki-roots'),
        'priority' => 20,
        'callback' => 'mr_product_tab_ingredients',
    ];
    $tabs['suggested_use'] = [
        'title'    => __('Suggested Use', 'meraki-roots'),
        'priority' => 30,
        'callback' => 'mr_product_tab_suggested_use',
    ];
    $tabs['warning'] = [
        'title'    => __('Warning', 'meraki-roots'),
        'priority' => 40,
        'callback' => 'mr_product_tab_warning',
    ];
    $tabs['lab_results'] = [
        'title'    => __('Lab Results', 'meraki-roots'),
        'priority' => 50,
        'callback' => 'mr_product_tab_lab_results',
    ];

    return $tabs;
}

function mr_product_tab_ingredients(): void {
    mr_render_product_meta_tab('_mr_ingredients', __('Ingredients are being updated.', 'meraki-roots'));
}

function mr_product_tab_suggested_use(): void {
    mr_render_product_meta_tab('_mr_suggested_use', __('Suggested use information is being updated.', 'meraki-roots'));
}

function mr_product_tab_warning(): void {
    mr_render_product_meta_tab('_mr_warning', __('Warning information is being updated.', 'meraki-roots'));
}

function mr_product_tab_lab_results(): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    get_template_part('template-parts/meraki/product-coa-callout', null, [
        'product'  => $product,
        'coa_file' => mr_get_product_meta($product, '_mr_coa_file'),
    ]);
}

function mr_render_product_meta_tab(string $meta_key, string $fallback): void {
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    $value = mr_get_product_meta($product, $meta_key, $fallback);
    echo mr_format_meta_paragraphs($value);
}

function mr_render_free_shipping_message(): void {
    echo '<div class="mr-free-shipping-message">' . esc_html__('Free shipping on orders over $100.', 'meraki-roots') . '</div>';
}

function mr_render_checkout_disclaimer(): void {
    echo '<p class="mr-checkout-disclaimer">' . esc_html__('By placing your order, you confirm that you have reviewed the product information, warnings, and lab results available on this site.', 'meraki-roots') . '</p>';
}
