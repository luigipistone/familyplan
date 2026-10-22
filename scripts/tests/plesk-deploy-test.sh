#!/usr/bin/env bash
# Integration test of file preservation and failure handling; no real PHP/MySQL/SSH.
set -euo pipefail
script=$(cd "$(dirname "$0")/.." && pwd)/plesk-deploy.sh
test_root=$(mktemp -d)
trap 'rm -rf "$test_root"' EXIT
export HOME="$test_root/home"
mkdir -p "$HOME" "$test_root/bin"
export PATH="$test_root/bin:$PATH"
printf '#!/bin/sh\nexit 0\n' > "$test_root/bin/mysqldump"
cat > "$test_root/bin/php" <<'PHP'
#!/usr/bin/env bash
set -eu
if [[ "$1" == -r ]]; then
    if [[ $# == 3 ]]; then
        [[ "${FAIL_BACKUP:-}" != 1 ]] || exit 1
        printf 'synthetic backup' > "$3/data.zip"
    fi
    exit 0
fi
echo "$*" >> "$PWD/commands.log"
case "$2" in
    down) touch storage/framework/down ;;
    up) rm -f storage/framework/down ;;
    migrate) [[ "${FAIL_MIGRATION:-}" != 1 ]] || exit 1 ;;
esac
PHP
chmod +x "$test_root/bin/"*

fixture() {
    app="$test_root/$1/app"
    release="$test_root/$1/release"
    mkdir -p "$app" "$release"
    for root in "$app" "$release"; do
        mkdir -p "$root/"{app,config,routes,resources,vendor,public/build,bootstrap/cache,storage/framework}
        touch "$root/artisan" "$root/vendor/autoload.php" "$root/public/build/manifest.json"
    done
    mkdir -p "$app/public/media" "$app/storage/app/public"
    printf 'existing-key' > "$app/.env"
    printf 'existing-config' > "$app/config.local.php"
    printf 'audio' > "$app/public/media/sound.mp3"
    printf 'photo' > "$app/storage/app/public/photo.jpg"
    ln -s ../storage/app/public "$app/public/storage"
    printf 'stale' > "$app/app/Old.php"
    printf 'new' > "$release/app/New.php"
    printf 'stale-asset' > "$app/public/build/old.js"
    tar -czf "$test_root/$1/release.tar.gz" -C "$release" .
    archive="$test_root/$1/release.tar.gz"
}

fixture check
bash "$script" "$app" "$test_root/bin/php" "$archive" check
test ! -f "$app/commands.log"
test -f "$app/app/Old.php"

fixture success
bash "$script" "$app" "$test_root/bin/php" "$archive" deploy
test "$(cat "$app/.env")" == existing-key
test "$(cat "$app/config.local.php")" == existing-config
test "$(cat "$app/public/media/sound.mp3")" == audio
test "$(cat "$app/storage/app/public/photo.jpg")" == photo
test -L "$app/public/storage"
test -f "$app/app/New.php" && test ! -e "$app/app/Old.php"
test ! -e "$app/public/build/old.js"
test ! -e "$app/storage/framework/down"
test "$(stat -c %a "$app/app/New.php")" == 644
rg_backup=$(find "$HOME/familyplan-deploy-backups" -name code-and-config.tar.gz -print -quit)
test -n "$rg_backup" && test "$(stat -c %a "$rg_backup")" == 600
tar -tzf "$rg_backup" | grep -q '^./.env$'

fixture backup-failure
if FAIL_BACKUP=1 bash "$script" "$app" "$test_root/bin/php" "$archive" deploy; then exit 1; fi
test -f "$app/app/Old.php" && test ! -e "$app/app/New.php"
test ! -e "$app/storage/framework/down"
! grep -q 'migrate' "$app/commands.log"

fixture migration-failure
if FAIL_MIGRATION=1 bash "$script" "$app" "$test_root/bin/php" "$archive" deploy; then exit 1; fi
test -f "$app/storage/framework/down"
! grep -q 'artisan up' "$app/commands.log"
echo 'Deploy integration checks passed.'
