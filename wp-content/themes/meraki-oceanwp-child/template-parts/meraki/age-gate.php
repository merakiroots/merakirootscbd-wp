<?php defined('ABSPATH') || exit; ?>
<div class="mr-age-gate" hidden>
    <div class="mr-age-gate__dialog" role="dialog" aria-modal="true" aria-labelledby="mr-age-gate-title">
        <h2 id="mr-age-gate-title"><?php esc_html_e('Are you 21 or older?', 'meraki-roots'); ?></h2>
        <p><?php esc_html_e('Some products on this site are intended for adult customers. Please confirm your age before continuing.', 'meraki-roots'); ?></p>
        <button type="button" class="mr-button" data-mr-age-confirm><?php esc_html_e('Yes, enter site', 'meraki-roots'); ?></button>
        <a class="mr-button mr-button--secondary" href="https://www.google.com/"><?php esc_html_e('No, leave site', 'meraki-roots'); ?></a>
    </div>
</div>
