# Hostinger Deployment Runbook (Meraki Commerce Core)

Date: 2026-05-02  
Environment: `merakirootscbd.com`  
Server: `u977999931@195.35.39.172:65002`  
WP root: `/home/u977999931/domains/merakirootscbd.com/public_html`

## 0. Goal and Guardrails

Deploy and validate:

1. `wp-content/plugins/meraki-commerce-core`
2. migration command `wp meraki coa migrate-legacy`

Optional (only if approved in same window):

1. `wp-content/themes/meraki-oceanwp-child`

Guardrails:

1. keep Hostinger read-only unless write window is explicitly open
2. `--dry-run` must make zero writes
3. all write phases include explicit checkpoint pauses

## 1. Local Validation (pre-package)

From repo root:

```bash
php -l wp-content/plugins/meraki-commerce-core/src/Domain/COA/CoaMigrationCommand.php
php wp-content/plugins/meraki-commerce-core/tests/run.php
```

## 2. Portable Packaging (safe top-level layout)

Create deterministic package roots so extraction cannot accidentally nest into unknown paths.

### 2.1 Bash / Linux / macOS

```bash
set -euo pipefail
mkdir -p dist/stage/plugins dist/stage/themes
rm -rf dist/stage/plugins/meraki-commerce-core dist/stage/themes/meraki-oceanwp-child
cp -a wp-content/plugins/meraki-commerce-core dist/stage/plugins/
cp -a wp-content/themes/meraki-oceanwp-child dist/stage/themes/
mkdir -p dist
(
  cd dist/stage/plugins
  zip -r ../../meraki-commerce-core.zip meraki-commerce-core
)
(
  cd dist/stage/themes
  zip -r ../../meraki-oceanwp-child.zip meraki-oceanwp-child
)
```

### 2.2 PowerShell (Windows)

```powershell
$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path dist\stage\plugins, dist\stage\themes, dist | Out-Null
Remove-Item -Recurse -Force dist\stage\plugins\meraki-commerce-core, dist\stage\themes\meraki-oceanwp-child -ErrorAction SilentlyContinue
Copy-Item -Recurse -Force wp-content\plugins\meraki-commerce-core dist\stage\plugins\
Copy-Item -Recurse -Force wp-content\themes\meraki-oceanwp-child dist\stage\themes\
if (Test-Path dist\meraki-commerce-core.zip) { Remove-Item -Force dist\meraki-commerce-core.zip }
if (Test-Path dist\meraki-oceanwp-child.zip) { Remove-Item -Force dist\meraki-oceanwp-child.zip }
Compress-Archive -Path dist\stage\plugins\meraki-commerce-core -DestinationPath dist\meraki-commerce-core.zip -CompressionLevel Optimal
Compress-Archive -Path dist\stage\themes\meraki-oceanwp-child -DestinationPath dist\meraki-oceanwp-child.zip -CompressionLevel Optimal
```

## 3. Upload Artifacts

```bash
scp -P 65002 dist/meraki-commerce-core.zip u977999931@195.35.39.172:/home/u977999931/private-meraki-launch/
scp -P 65002 dist/meraki-oceanwp-child.zip u977999931@195.35.39.172:/home/u977999931/private-meraki-launch/
```

## 4. Connect and Set Session Variables

```bash
ssh -p 65002 u977999931@195.35.39.172
```

Inside SSH shell:

```bash
set -euo pipefail
export WP_ROOT=/home/u977999931/domains/merakirootscbd.com/public_html
export PRIVATE_ROOT=/home/u977999931/private-meraki-launch
export RUN_TS="$(date +%F-%H%M%S)"
export STAGE_DIR="$PRIVATE_ROOT/staging-$RUN_TS"
export BACKUP_DIR="$PRIVATE_ROOT/backups/$RUN_TS"
mkdir -p "$STAGE_DIR" "$BACKUP_DIR"
cd "$WP_ROOT"
```

## 5. Preflight (read-only)

```bash
wp core version --allow-root
wp option get siteurl --allow-root
wp theme list --status=active --allow-root
wp plugin list --status=active --allow-root
wp post list --post_type=mr_coa --format=count --allow-root | tee "$STAGE_DIR/mr_coa_count_before.txt"
find wp-content/uploads/lab-results -type f | wc -l | tee "$STAGE_DIR/lab_results_file_count.txt"
```

## 6. Backup (write step)

```bash
cd "$WP_ROOT"
tar -czf "$BACKUP_DIR/public_html-pre-meraki-commerce-core.tgz" .
DB_NAME="$(wp config get DB_NAME --type=constant --allow-root | tr -d '\r')"
DB_USER="$(wp config get DB_USER --type=constant --allow-root | tr -d '\r')"
DB_PASS="$(wp config get DB_PASSWORD --type=constant --allow-root | tr -d '\r')"
DB_HOST="$(wp config get DB_HOST --type=constant --allow-root | tr -d '\r')"
mysqldump --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" --single-transaction --quick --skip-lock-tables "$DB_NAME" > "$BACKUP_DIR/db-pre-meraki-commerce-core.sql"
sha256sum "$BACKUP_DIR/public_html-pre-meraki-commerce-core.tgz" "$BACKUP_DIR/db-pre-meraki-commerce-core.sql" | tee "$BACKUP_DIR/SHA256SUMS.txt"
```

Notes:

- Use `mysqldump` here because Hostinger returned exit 255 for `wp db export` during validation.
- Keep the generated `SHA256SUMS.txt` with the backup set so restore inputs can be verified later.

## 7. Live Migration

```bash
cd "$WP_ROOT"
wp meraki coa migrate-legacy --create-missing-attachments --allow-root | tee "$STAGE_DIR/migration-live.log"
```

## 8. Final Validation

```bash
cd "$WP_ROOT"
wp post list --post_type=mr_coa --fields=ID,post_title,post_status --allow-root | tee "$STAGE_DIR/mr_coa_posts_after_live.txt"
wp db query "SELECT COUNT(*) AS products_with_current_coa FROM wp_postmeta WHERE meta_key='_mr_current_coa_id' AND meta_value<>'';" --allow-root | tee "$STAGE_DIR/current_coa_assignment_count.txt"
wp cache flush --allow-root || true
wp litespeed-purge all --allow-root || true
```

## 9. Rollback

```bash
cd "$WP_ROOT"
wp plugin deactivate meraki-commerce-core --allow-root || true
DB_NAME="$(wp config get DB_NAME --type=constant --allow-root | tr -d '\r')"
DB_USER="$(wp config get DB_USER --type=constant --allow-root | tr -d '\r')"
DB_PASS="$(wp config get DB_PASSWORD --type=constant --allow-root | tr -d '\r')"
DB_HOST="$(wp config get DB_HOST --type=constant --allow-root | tr -d '\r')"
mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" < "$BACKUP_DIR/db-pre-meraki-commerce-core.sql"
find "$WP_ROOT" -mindepth 1 -maxdepth 1 -exec rm -rf {} +
tar -xzf "$BACKUP_DIR/public_html-pre-meraki-commerce-core.tgz" -C "$WP_ROOT"
wp post list --post_type=mr_coa --format=count --allow-root
```