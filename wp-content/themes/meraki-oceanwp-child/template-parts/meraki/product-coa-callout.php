<?php
/** @var array $args */
defined('ABSPATH') || exit;

$product  = $args['product'] ?? null;
$coa_file = $args['coa_file'] ?? '';

if (!$product instanceof WC_Product || !$coa_file) {
    return;
}

$lab  = mr_get_product_meta($product, '_mr_coa_lab_name', 'Third-party lab');
$date = mr_get_product_meta($product, '_mr_coa_test_date');
$thc  = mr_get_product_meta($product, '_mr_total_thc_status');
$cbd  = mr_get_product_meta($product, '_mr_total_cbd');
?>
<div class="mr-coa-callout">
    <div>
        <span class="mr-kicker"><?php esc_html_e('Third-Party Lab Result', 'meraki-roots'); ?></span>
        <h3><?php esc_html_e('Know what is in your CBD.', 'meraki-roots'); ?></h3>
        <dl class="mr-coa-callout__meta">
            <div><dt><?php esc_html_e('Lab', 'meraki-roots'); ?></dt><dd><?php echo esc_html($lab); ?></dd></div>
            <?php if ($date) : ?><div><dt><?php esc_html_e('Date', 'meraki-roots'); ?></dt><dd><?php echo esc_html($date); ?></dd></div><?php endif; ?>
            <?php if ($thc) : ?><div><dt><?php esc_html_e('THC', 'meraki-roots'); ?></dt><dd><?php echo esc_html($thc); ?></dd></div><?php endif; ?>
            <?php if ($cbd) : ?><div><dt><?php esc_html_e('CBD', 'meraki-roots'); ?></dt><dd><?php echo esc_html($cbd); ?></dd></div><?php endif; ?>
        </dl>
    </div>
    <a class="mr-button" href="<?php echo esc_url($coa_file); ?>" target="_blank" rel="noopener">
        <?php esc_html_e('View COA', 'meraki-roots'); ?>
    </a>
</div>
