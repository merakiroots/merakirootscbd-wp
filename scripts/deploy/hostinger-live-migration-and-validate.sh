#!/usr/bin/env bash
set -euo pipefail

WP_ROOT=/home/u977999931/domains/merakirootscbd.com/public_html
PRIVATE_ROOT=/home/u977999931/private-meraki-launch
RUN_TS="$(date +%F-%H%M%S)"
STAGE_DIR="$PRIVATE_ROOT/deploy-runs/$RUN_TS-live"
mkdir -p "$STAGE_DIR"
cd "$WP_ROOT"

MR_COA_BEFORE="$(wp post list --post_type=mr_coa --format=count --allow-root | tr -d '\r')"

wp meraki coa migrate-legacy --create-missing-attachments --allow-root | tee "$STAGE_DIR/migration-live.log"

MR_COA_AFTER="$(wp post list --post_type=mr_coa --format=count --allow-root | tr -d '\r')"
PRODUCTS_WITH_CURRENT_COA="$(wp db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='_mr_current_coa_id' AND meta_value<>'';" --skip-column-names --allow-root | tr -d '\r')"
ATTACHMENT_COUNT="$(wp db query "SELECT COUNT(*) FROM wp_posts p INNER JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE p.post_type='attachment' AND pm.meta_key='_wp_attached_file' AND pm.meta_value LIKE 'lab-results/%';" --skip-column-names --allow-root | tr -d '\r')"

LIVE_LOG="$STAGE_DIR/migration-live.log"
PROCESSED="$(grep '^processed:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
MIGRATED="$(grep '^migrated:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
SKIP_NO_LEGACY="$(grep '^skipped_no_legacy:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
SKIP_LINKED="$(grep '^skipped_already_linked:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
MISSING_ATTACHMENT="$(grep '^missing_attachment:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
CREATED_COA="$(grep '^created_coa_posts:' "$LIVE_LOG" | awk '{print $2}' | tail -n1)"
WARNINGS_COUNT="$(grep -c '^Warning:' "$LIVE_LOG" || true)"

wp post list --post_type=mr_coa --fields=ID,post_title,post_status --allow-root | tee "$STAGE_DIR/mr_coa_posts_after_live.txt"
wp cache flush --allow-root || true
wp litespeed-purge all --allow-root || true

printf 'SUMMARY_STAGE_DIR=%s\n' "$STAGE_DIR"
printf 'SUMMARY_LIVE_PROCESSED=%s\n' "$PROCESSED"
printf 'SUMMARY_LIVE_MIGRATED=%s\n' "$MIGRATED"
printf 'SUMMARY_LIVE_SKIPPED_NO_LEGACY=%s\n' "$SKIP_NO_LEGACY"
printf 'SUMMARY_LIVE_SKIPPED_ALREADY_LINKED=%s\n' "$SKIP_LINKED"
printf 'SUMMARY_LIVE_MISSING_ATTACHMENT=%s\n' "$MISSING_ATTACHMENT"
printf 'SUMMARY_LIVE_CREATED_COA_POSTS=%s\n' "$CREATED_COA"
printf 'SUMMARY_LIVE_WARNINGS_COUNT=%s\n' "$WARNINGS_COUNT"
printf 'SUMMARY_MR_COA_BEFORE=%s\n' "$MR_COA_BEFORE"
printf 'SUMMARY_MR_COA_AFTER=%s\n' "$MR_COA_AFTER"
printf 'SUMMARY_PRODUCTS_WITH_CURRENT_COA=%s\n' "$PRODUCTS_WITH_CURRENT_COA"
printf 'SUMMARY_LAB_RESULTS_ATTACHMENT_COUNT=%s\n' "$ATTACHMENT_COUNT"