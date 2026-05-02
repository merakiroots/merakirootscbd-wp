<?php

defined('ABSPATH') || exit;

add_shortcode('meraki_lab_results', function (): string {
    ob_start();
    get_template_part('template-parts/meraki/lab-results-list');
    return ob_get_clean();
});
