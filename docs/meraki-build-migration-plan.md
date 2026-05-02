# Meraki Build and Migration Plan (2026-05-02)

## Scope and Constraints
- Hostinger remains read-only until explicit approval for write/deploy.
- Plugin-owned contracts are the target; theme remains launch bridge.
- Source of truth used in this plan:
  - Hostinger SSH read-only audit (`docs/hostinger-readonly-audit-results-2026-05-02.md`)
  - public probes (`https://merakirootscbd.com`, `wp-json`)
  - local source bundle and imports
  - build brief (`01-meraki-codex-build-brief-2-.docx`)

## Confirmed Production Reality
- WordPress root: `domains/merakirootscbd.com/public_html`
- Core: `6.9.4`
- Active theme: `meraki-oceanwp-child` `1.2.0` (OceanWP parent `4.1.5`)
- Active plugin stack: WooCommerce + Ocean extensions + Hostinger suite + Redirection + Yoast
- `mr_coa` post type records: `0`
- `lab-results` upload files: `30`
- DB host shape: `127.0.0.1` with local DB user/db name constants in `wp-config.php`

## Reality Check Matrix

| Area | Hostinger (read-only audit) | GitHub repo before this pass | After this pass |
|---|---|---|---|
| Theme bridge | `meraki-oceanwp-child` active | only placeholder `my-theme` | `meraki-oceanwp-child` copied into repo for bridge parity |
| COA data ownership | theme/meta/file-url driven, no `mr_coa` posts | no normalized plugin layer | `meraki-commerce-core` plugin scaffolded |
| Lab results source | 30 static COA PDFs under uploads | no structured COA records | migration command prepared to create/link `mr_coa` records |
| Product trust schema | `_mr_*` fields used in runtime | unregistered in repo baseline | explicit `register_post_meta` contracts added |
| Migration tooling | none deployed | none | `wp meraki coa migrate-legacy` with dry-run and targeting |

## Implemented in Repo

### Workstream A (plugin-owned contracts)
1. Added `wp-content/plugins/meraki-commerce-core` plugin scaffold.
2. Registered `mr_coa` post type and COA metadata schema.
3. Registered product trust + COA-related `_mr_*` metadata schema.
4. Added COA admin metabox and product-side `Current COA` selector.
5. Added normalized presenter and REST fields for product/COA output.
6. Added migration command:
   - `wp meraki coa migrate-legacy`
   - supports `--dry-run`, `--product_ids`, `--create-missing-attachments`, `--force-relink`
7. Added plugin-owned `[meraki_lab_results]` shortcode/query path.
8. Added bridge override logic so plugin-owned COA callout/tab output supersedes theme legacy callout.

### Bridge parity
1. Synced `meraki-oceanwp-child` into repo from the production-matching package (`wp-content.zip`) so repo reflects live launch bridge theme (`Version: 1.2.0` plus v3-v6 patch assets).

### Testing and validation
1. PHP lint pass across `meraki-commerce-core`.
2. Normalization test harness pass (`tests/run.php`).

## Next Execution Steps (still no Hostinger writes)
1. Run local dockerized WP + activate bridge theme and `meraki-commerce-core`.
2. Seed local uploads/import data from source bundle and confirm shortcode/callout behavior.
3. Dry-run migration command locally and capture deterministic output sample.
4. Produce deploy runbook for approved Hostinger write window.

## Definition of Done for Current Phase
- Hostinger reality has been verified in read-only mode.
- Repo now contains both:
  - launch bridge theme parity
  - plugin-owned COA data layer foundation
- Migration command and tests are implemented and executable locally.
- No Hostinger writes were performed.
