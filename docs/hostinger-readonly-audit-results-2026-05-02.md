# Hostinger Read-Only Audit Results (2026-05-02)

## Connection and Host
- SSH target: `u977999931@195.35.39.172:65002`
- Hostname: `us-phx-web1169.main-hosting.eu`
- Home directory: `/home/u977999931`

## WordPress Location and Core
- `wp-config.php`: `./domains/merakirootscbd.com/public_html/wp-config.php`
- WordPress root: `./domains/merakirootscbd.com/public_html`
- WordPress version: `6.9.4`
- `siteurl`: `https://merakirootscbd.com`
- `home`: `https://merakirootscbd.com`

## Theme State
- Active theme: `meraki-oceanwp-child` version `1.2.0`
- Parent theme: `oceanwp` version `4.1.5`

## Plugin State
### Active
- `hostinger-ai-assistant`
- `hostinger-easy-onboarding`
- `hostinger-reach`
- `hostinger`
- `litespeed-cache`
- `ocean-extra`
- `ocean-modal-window`
- `ocean-posts-slider`
- `ocean-product-sharing`
- `ocean-social-sharing`
- `ocean-stick-anything`
- `redirection`
- `woocommerce`
- `woo-update-manager`
- `wpforms-lite`
- `wordpress-seo`

### Inactive (high signal)
- `advanced-custom-fields`
- `jetpack`
- `google-site-kit`
- `facebook-for-woocommerce`
- `woocommerce-shipping`
- `woocommerce-services`

## COA / Lab Results State
- `mr_coa` posts: none (`0` records)
- `wp-content/uploads/lab-results` files: `30`
- `wp-content/uploads/wc-imports` contains:
  - `product-meraki_coa_upload_map-mkka2wb4u1.csv`
  - `product-meraki_woocommerce_product_content_update_by_sku-md5jdgnuij.csv`
  - `product-meraki_woocommerce_products_import_ready-9kivzhnsld.csv`

## wp-config shape (redacted)
- `DB_NAME='u977999931_HuwI8'`
- `DB_USER='u977999931_Ikd1I'`
- `DB_PASSWORD='***REDACTED***'`
- `DB_HOST='127.0.0.1'`
- `WP_DEBUG=false`

## Implications for Build
- Production currently depends on theme-owned COA/product meta and file URLs.
- No plugin-owned normalized COA layer is deployed yet.
- Migration can be deterministic from existing `_mr_coa_file` style metadata and uploads map.
- Implementation can proceed in GitHub repo now; deployment to Hostinger remains blocked pending explicit write approval.