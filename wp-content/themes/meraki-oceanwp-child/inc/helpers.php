<?php

defined('ABSPATH') || exit;

function mr_get_asset_uri(string $path): string {
    return get_stylesheet_directory_uri() . '/assets/' . ltrim($path, '/');
}

function mr_get_product_meta(?WC_Product $product, string $key, string $default = ''): string {
    if (!$product) {
        return $default;
    }

    $value = get_post_meta($product->get_id(), $key, true);

    if (is_array($value)) {
        $value = implode(', ', array_filter(array_map('strval', $value)));
    }

    if (is_scalar($value)) {
        $value = trim((string) $value);
        return $value !== '' ? $value : $default;
    }

    return $default;
}

function mr_get_logo_html(string $class = 'mr-logo'): string {
    $custom_logo = get_custom_logo();

    if ($custom_logo) {
        return '<div class="' . esc_attr($class) . '">' . $custom_logo . '</div>';
    }

    return sprintf(
        '<a class="%1$s mr-logo--text" href="%2$s" aria-label="%3$s">%4$s</a>',
        esc_attr($class),
        esc_url(home_url('/')),
        esc_attr(get_bloginfo('name')),
        esc_html(get_bloginfo('name'))
    );
}

function mr_render_fallback_menu(array $items): void {
    echo '<ul class="mr-menu mr-menu--fallback">';
    foreach ($items as $label => $url) {
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
}


function mr_wc_cart_url(): string {
    return function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
}

function mr_wc_my_account_url(): string {
    return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
}

function mr_safe_html(string $html): string {
    return wp_kses($html, [
        'a' => [
            'href' => [],
            'title' => [],
            'target' => [],
            'rel' => [],
            'class' => [],
        ],
        'br' => [],
        'p' => ['class' => []],
        'strong' => [],
        'em' => [],
        'span' => ['class' => []],
        'ul' => ['class' => []],
        'ol' => ['class' => []],
        'li' => ['class' => []],
        'h2' => ['class' => []],
        'h3' => ['class' => []],
        'h4' => ['class' => []],
        'div' => ['class' => [], 'id' => []],
    ]);
}

function mr_format_meta_paragraphs(string $value): string {
    if ($value === '') {
        return '';
    }

    return mr_safe_html(wpautop($value));
}
