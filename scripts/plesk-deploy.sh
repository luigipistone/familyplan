#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
stage='arguments'
trap 'echo "Plesk failed during $stage (line $LINENO)." >&2' ERR
require() {
    local message=$1
    shift
    if ! "$@"; then
        echo "Plesk prerequisite failed: $message" >&2
        return 1
    fi
}

app=${1:?Missing Laravel directory}
php=${2:?Missing PHP binary}
archive=${3:?Missing release archive}
operation=${4:-check}
[[ "$operation" == check || "$operation" == deploy ]]
stage='Laravel directory'
cd "$app"
app=$PWD
echo 'Plesk: checking server prerequisites.'
stage='server tools'
for tool in rsync tar flock mysqldump; do
    require "tool $tool unavailable in noninteractive PATH" command -v "$tool" >/dev/null
done
require 'PHP binary not executable' test -x "$php"
stage='application files and permissions'
for file in artisan .env config.local.php vendor/autoload.php; do
    require "missing file $file" test -f "$file"
    require "unreadable file $file" test -r "$file"
done
for directory in storage public bootstrap/cache; do
    require "missing directory $directory" test -d "$directory"
done
for directory in . storage bootstrap/cache; do
    require "directory $directory not writable" test -w "$directory"
done
stage='PHP, Laravel and MySQL'
"$php" -r '
if (PHP_MAJOR_VERSION !== 8 || PHP_MINOR_VERSION !== 3) {
    fwrite(STDERR, "PHP 8.3 required; got ".PHP_VERSION."\n"); exit(1);
}
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
stage='deployment (see failing line)'

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
"$php" artisan familyplan:prune-automatic-backups
"$php" artisan up
maintenance=false
# Keep five complete rollback sets; never touch manual folders or incomplete sets.
rollback_sets=()
for candidate in "$HOME/familyplan-deploy-backups/"*; do
    [[ -d "$candidate" && ! -L "$candidate" ]] || continue
    [[ "${candidate##*/}" =~ ^[0-9]{8}T[0-9]{6}Z-[0-9]+$ ]] || continue
    [[ -f "$candidate/code-and-config.tar.gz" && -f "$candidate/data.zip" ]] || continue
    rollback_sets+=("$candidate")
done
if (( ${#rollback_sets[@]} > 5 )); then
    mapfile -t rollback_sets < <(printf '%s\n' "${rollback_sets[@]}" | LC_ALL=C sort -r)
    for candidate in "${rollback_sets[@]:5}"; do
        rm -rf -- "$candidate"
    done
fi
echo "Deploy completed. Backup: $backup"
