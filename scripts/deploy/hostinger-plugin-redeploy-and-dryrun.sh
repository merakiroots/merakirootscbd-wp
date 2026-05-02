#!/usr/bin/env bash
set -euo pipefail

WP_ROOT=/home/u977999931/domains/merakirootscbd.com/public_html
PRIVATE_ROOT=/home/u977999931/private-meraki-launch
RUN_TS="$(date +%F-%H%M%S)"
STAGE_DIR="$PRIVATE_ROOT/deploy-runs/$RUN_TS"
mkdir -p "$STAGE_DIR"
cd "$WP_ROOT"

safe_unzip_extract() {
  local zip_file="$1"
  local expected_leaf="$2"
  local out_var="$3"
  local unpack_dir="$STAGE_DIR/unpack-$expected_leaf"
  rm -rf "$unpack_dir"
  mkdir -p "$unpack_dir"

  if unzip -Z1 "$zip_file" | grep -E '(^/|(^|/)\.\.(/|$))' >/dev/null; then
    echo "ERROR: zip contains unsafe path(s): $zip_file" >&2
    return 1
  fi

  unzip -q "$zip_file" -d "$unpack_dir"

  local resolved=""
  if [ -d "$unpack_dir/$expected_leaf" ]; then
    resolved="$unpack_dir/$expected_leaf"
  else
    resolved="$(find "$unpack_dir" -mindepth 2 -maxdepth 8 -type d -name "$expected_leaf" | head -n1 || true)"
  fi

  if [ -z "$resolved" ] || [ ! -d "$resolved" ]; then
    echo "ERROR: expected folder '$expected_leaf' not found in $zip_file" >&2
    return 1
  fi

  printf -v "$out_var" '%s' "$resolved"
}

PLUGIN_ZIP="$PRIVATE_ROOT/meraki-commerce-core.zip"
safe_unzip_extract "$PLUGIN_ZIP" "meraki-commerce-core" PLUGIN_SRC
PLUGIN_DST="$WP_ROOT/wp-content/plugins/meraki-commerce-core"
rm -rf "$PLUGIN_DST"
mkdir -p "$PLUGIN_DST"
cp -a "$PLUGIN_SRC"/. "$PLUGIN_DST"/
find "$PLUGIN_DST" -type d -exec chmod 755 {} \;
find "$PLUGIN_DST" -type f -exec chmod 644 {} \;

if ! wp plugin is-active meraki-commerce-core --allow-root >/dev/null 2>&1; then
  wp plugin activate meraki-commerce-core --allow-root | tee "$STAGE_DIR/plugin-activation.txt"
else
  echo "Plugin already active" | tee "$STAGE_DIR/plugin-activation.txt"
fi

MR_COA_BEFORE="$(wp post list --post_type=mr_coa --format=count --allow-root | tr -d '\r')"
wp meraki coa migrate-legacy --dry-run --allow-root | tee "$STAGE_DIR/migration-dry-run.log"
MR_COA_AFTER="$(wp post list --post_type=mr_coa --format=count --allow-root | tr -d '\r')"

printf 'SUMMARY_STAGE_DIR=%s\n' "$STAGE_DIR"
printf 'SUMMARY_MR_COA_BEFORE=%s\n' "$MR_COA_BEFORE"
printf 'SUMMARY_MR_COA_AFTER=%s\n' "$MR_COA_AFTER"