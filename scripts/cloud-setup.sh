#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [ -f config.local.php ]; then
  echo 'Remove production config.local.php from the cloud checkout before setup.' >&2
  exit 1
fi
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public
if [ ! -f .env ]; then
  cp .env.example .env
fi
export APP_ENV=local
export APP_URL=http://localhost:8000
export DB_CONNECTION=sqlite
export DB_DATABASE="$PWD/database/database.sqlite"
export SESSION_SECURE_COOKIE=false
export MAIL_MAILER=log
export CACHE_STORE=file
export SESSION_DRIVER=file
export QUEUE_CONNECTION=sync
touch "$DB_DATABASE"
composer install --no-interaction --prefer-dist
php artisan config:clear
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi
php artisan migrate --force
npm ci
npm run build
echo 'Ready. Use the same environment variables to run php artisan serve --host=0.0.0.0.'
