# `public_html` and `private-meraki-launch` Inventory (2026-05-02)

## Source of evidence
- Hostinger File Browser (authenticated Playwright session)
- Hostinger read-only WP-CLI audit (SSH)

## `public_html` (confirmed)
Top-level directories:
- `.private`
- `staging`
- `wp-admin`
- `wp-content`
- `wp-includes`

Top-level files (high signal):
- `.htaccess`
- `.htaccess.bk`
- `wp-config.php`
- standard WP core entry files (`index.php`, `wp-load.php`, `wp-login.php`, etc.)

### `public_html/wp-content`
Directories:
- `jetpack-waf`
- `litespeed`
- `mu-plugins`
- `plugins`
- `themes`
- `upgrade`
- `upgrade-temp-backup`
- `uploads`

Files (high signal):
- `.litespeed_conf.dat`
- `debug.log`
- `mcp.log`
- `object-cache.php`

### `public_html/wp-content/themes`
- `meraki-oceanwp-child`
- `oceanwp`

### `public_html/wp-content/themes/meraki-oceanwp-child`
Folders:
- `assets`
- `inc`
- `page-templates`
- `template-parts`
- `woocommerce`

Files:
- `style.css` (`Version: 1.2.0`)
- `functions.php`
- `header.php`
- `footer.php`
- `README.md`
- `index.php`

#### `assets/css` includes launch patch set
- `meraki-base.css`
- `meraki-footer.css`
- `meraki-header.css`
- `meraki-pages.css`
- `meraki-responsive.css`
- `meraki-woocommerce.css`
- `meraki-launch-polish.css`
- `meraki-launch-polish-v2.css`
- `meraki-header-sticky-fix-v3.css`
- `meraki-header-exact-fix-v4.css`
- `meraki-header-final-fix-v5.css`
- `meraki-header-shopify-behavior-v6.css`

#### `assets/js` includes launch patch set
- `meraki-mobile-menu.js`
- `meraki-accordions.js`
- `meraki-launch-polish-v2.js`
- `meraki-header-sticky-fix-v3.js`
- `meraki-header-exact-fix-v4.js`
- `meraki-header-final-fix-v5.js`
- `meraki-header-shopify-behavior-v6.js`

### `public_html/wp-content/plugins` (folder inventory)
- `advanced-custom-fields`
- `facebook-for-woocommerce`
- `google-site-kit`
- `hostinger`
- `hostinger-ai-assistant`
- `hostinger-easy-onboarding`
- `hostinger-reach`
- `image-optimization`
- `jetpack`
- `litespeed-cache`
- `ocean-extra`
- `ocean-modal-window`
- `ocean-posts-slider`
- `ocean-product-sharing`
- `ocean-social-sharing`
- `ocean-stick-anything`
- `reddit-for-woocommerce`
- `redirection`
- `snapchat-for-woocommerce`
- `woo-update-manager`
- `woocommerce`
- `woocommerce-services`
- `woocommerce-shipping`
- `wordpress-seo`
- `wpforms-lite`

## `private-meraki-launch` (confirmed)
Top-level folders:
- `meraki_clean_launch_package_v1_2_0`
- `meraki_frontend_assets_upload_pack`
- `meraki_header_shopify_behavior_v6`

### `private-meraki-launch/meraki_clean_launch_package_v1_2_0`
- folders: `docs`, `imports`, `media`, `scripts`, `themes`
- file: `README_START_HERE.md`

### `private-meraki-launch/meraki_frontend_assets_upload_pack`
- folders: `docs`, `imports`, `media`, `scripts`, `meraki_frontend_assets_upload_pack`
- file: `README.md`

### `private-meraki-launch/meraki_header_shopify_behavior_v6`
- folders: `assets`, `scripts`
- file: `README.md`

## Key deltas and implications
1. Production `meraki-oceanwp-child` includes extra v3-v6 CSS/JS patch files not present in the earlier reviewed theme source.
2. `private-meraki-launch` acts as a package vault: clean launch package + frontend assets pack + header behavior patch pack.
3. No plugin-owned `mr_coa` records exist in production yet (`0` records from WP-CLI), so migration path should start from theme/meta/upload state.