<?php

defined('ABSPATH') || exit;

function mr_coa_meta_keys(): array {
    return [
        '_mr_coa_file',
        '_mr_coa_batch_id',
        '_mr_coa_test_date',
        '_mr_coa_lab_name',
        '_mr_total_cbd',
        '_mr_total_thc_status',
        '_mr_delta9_thc_status',
        '_mr_coa_category',
    ];
}

function mr_product_has_coa(?WC_Product $product): bool {
    return $product && mr_get_product_meta($product, '_mr_coa_file') !== '';
}
