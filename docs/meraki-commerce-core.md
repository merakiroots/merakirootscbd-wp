# Meraki Commerce Core

## Purpose
`meraki-commerce-core` is the plugin-owned source of truth for COA and product trust data.

## Main Components
- `Domain/COA/CoaPostType.php`: registers `mr_coa` records.
- `Domain/COA/CoaMetaRegistrar.php`: registers COA meta schemas.
- `Domain/ProductMeta/ProductMetaRegistrar.php`: registers product meta schemas.
- `Domain/COA/CoaAdminMetaBox.php`: admin editing for COA fields.
- `Domain/COA/ProductCoaPanel.php`: product-level current COA assignment.
- `Domain/COA/CoaMigrationCommand.php`: legacy URL migration command.
- `Domain/Frontend/LabResultsShortcode.php`: `[meraki_lab_results]` output.
- `Domain/Frontend/ProductCoaPresenter.php`: normalized product COA context.
- `Domain/Rest/RestFields.php`: REST exposure for product/COA data.

## WP-CLI Migration

```bash
wp meraki coa migrate-legacy --dry-run
wp meraki coa migrate-legacy --product_ids=123,124 --dry-run
wp meraki coa migrate-legacy --create-missing-attachments --force-relink
```

## Local Validation

```bash
php tests/run.php
```

## Notes
- Current theme can remain as launch bridge while plugin data contracts become authoritative.
- Hostinger deploy/write steps should remain paused until explicit approval.