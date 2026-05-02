# Hostinger Read-Only Audit Commands

Use only read-only commands until deploy approval.

## SSH Connect

```bash
ssh -p 65002 u977999931@195.35.39.172
```

## Server and WP Path Discovery

```bash
whoami
hostname
pwd
ls -la
find . -maxdepth 4 -name wp-config.php 2>/dev/null
find . -maxdepth 4 -type d -name wp-content 2>/dev/null
```

## WordPress Runtime Shape (read-only)

Run these from the detected WordPress root:

```bash
wp core version --allow-root
wp option get siteurl --allow-root
wp option get home --allow-root
wp theme list --status=active --allow-root
wp plugin list --status=active --allow-root
wp plugin list --allow-root
wp post list --post_type=mr_coa --fields=ID,post_title,post_status --allow-root
```

## COA and Upload State

```bash
find wp-content/uploads/lab-results -type f | wc -l
find wp-content/uploads/lab-results -type f | head -50
ls -la wp-content/uploads/wc-imports | sed -n '1,80p'
wp post meta list $(wp post list --post_type=product --posts_per_page=20 --field=ID --allow-root | tr '\n' ' ') --allow-root 2>/dev/null | grep '_mr_' | head -120
```

## wp-config Environment Shape (redact secrets)

```bash
php -r '
$cfg=file_get_contents("wp-config.php");
$patterns=["DB_NAME","DB_USER","DB_PASSWORD","DB_HOST","WP_HOME","WP_SITEURL","WP_DEBUG","DISALLOW_FILE_EDIT"];
foreach($patterns as $p){
  if(preg_match("/define\\(\\s*\"$p\"\\s*,\\s*(.+?)\\s*\\)\\s*;/",$cfg,$m)){
    $v=trim($m[1]);
    if($p==="DB_PASSWORD"){$v="\"***REDACTED***\"";}
    echo "$p=$v\n";
  }
}
'
```

## Optional plugin namespace check

```bash
wp eval 'print_r(array_keys(rest_get_server()->get_namespaces()));' --allow-root
```