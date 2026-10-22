#!/usr/bin/env bash
set -euo pipefail
umask 077

app=${1:?Missing Laravel directory}
php=${2:?Missing PHP binary}
archive=${3:?Missing release archive}
operation=${4:-check}
[[ "$operation" == check || "$operation" == deploy ]]
cd "$app"
app=$PWD
for tool in rsync tar flock mysqldump; do command -v "$tool" >/dev/null; done
test -x "$php"
test -f artisan && test -f .env && test -f config.local.php
test -f vendor/autoload.php && test -d storage && test -d public
test -w . && test -w storage && test -w bootstrap/cache
"$php" -r '
if (PHP_MAJOR_VERSION !== 8 || PHP_MINOR_VERSION !== 3) exit(1);
foreach (["pdo_mysql", "mbstring", "xml", "curl", "zip"] as $ext) {
    if (!extension_loaded($ext)) { fwrite(STDERR, "Missing extension: $ext\n"); exit(1); }
}
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!$app->environment("production") || !config("app.key") || config("database.default") !== "mysql") {
    fwrite(STDERR, "Expected production, existing APP_KEY and MySQL.\n"); exit(1);
}
Illuminate\Support\Facades\DB::connection()->getPdo();
'
echo 'Plesk preflight OK (PHP 8.3, production, APP_KEY, MySQL).'
[[ "$operation" == deploy ]] || exit 0

# Serialize deployments on the server, including runs from other clients.
exec 9>storage/framework/familyplan-deploy.lock
flock -n 9 || { echo 'Another deployment is running.' >&2; exit 1; }
test ! -f storage/framework/down || { echo 'Application already in maintenance.' >&2; exit 1; }
release=$(mktemp -d "${archive%/*}/release.XXXXXXXX")
umask 022
tar -xzf "$archive" -C "$release"
umask 077
for file in artisan vendor/autoload.php public/build/manifest.json; do test -f "$release/$file"; done
test ! -e "$release/.env" && test ! -e "$release/config.local.php"
protected_before=$(sha256sum .env config.local.php)
backup="$HOME/familyplan-deploy-backups/$(date -u +%Y%m%dT%H%M%SZ)-$$"
mkdir -p "$backup"
chmod 700 "$HOME/familyplan-deploy-backups" "$backup"
maintenance=false
changes_started=false
finish() {
    result=$?
    if (( result != 0 )) && [[ "$maintenance" == true ]]; then
        if [[ "$changes_started" == false ]]; then
            "$php" artisan up || true
            echo 'Deploy stopped before code changes; requested maintenance removal.' >&2
        else
            echo "Deploy failed; maintenance kept active. Backup: $backup" >&2
            echo 'Inspect the failure and restore code/database as needed before artisan up.' >&2
        fi
    fi
}
trap finish EXIT
"$php" artisan down --retry=60
maintenance=true

# Use the existing application's backup implementation before replacing any code.
"$php" -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $name = $app->make(App\Support\BackupManager::class)->createBackup("pre-deploy");
    if (!copy(storage_path("app/backups/".$name), $argv[1]."/data.zip")) exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, "Pre-deploy backup failed; inspect server logs and database tools.\n"); exit(1);
}
' "$backup"
tar --exclude=./storage --exclude=./public/media --exclude=./public/storage \
    --exclude=./.git --exclude=./node_modules --exclude=./backups \
    -czf "$backup/code-and-config.tar.gz" .

# Delete stale application classes and hashed Vite assets only in code directories.
# Never delete at the application root or in public: they contain persistent data.
umask 022
changes_started=true
for directory in app config routes resources vendor public/build; do
    rsync -rlt --delete "$release/$directory/" "$app/$directory/"
done
rsync -rlt --exclude=/.git/ --exclude=/.github/ --exclude=/.env --exclude=/.env.* \
    --exclude=/config.local.php --exclude=/storage/ --exclude=/public/media/ \
    --exclude=/public/storage --exclude=/backups/ --exclude=/node_modules/ \
    --exclude=/bootstrap/cache/ "$release/" "$app/"
[[ "$(sha256sum .env config.local.php)" == "$protected_before" ]]
"$php" artisan optimize:clear
"$php" artisan package:discover --ansi
"$php" artisan migrate --force
"$php" artisan config:cache
"$php" artisan route:cache
"$php" artisan view:cache
"$php" artisan queue:restart
"$php" artisan up
maintenance=false
echo "Deploy completed. Backup: $backup"
