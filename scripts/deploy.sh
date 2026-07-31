#!/usr/bin/env bash
# Runs ON THE SERVER (invoked over SSH by .github/workflows/deploy.yml) after the
# repo has been cloned/updated at $APP_DIR. Never touches DB credentials or the
# admin account — those are only ever entered by hand through /setup.
set -euo pipefail

APP_DIR="$HOME/domains/hardwarecenters.com/app"
PUBLIC_DIR="$HOME/domains/hardwarecenters.com/public_html"
BACKEND_DIR="$APP_DIR/backend"

cd "$BACKEND_DIR"

# The server's default `php` (and therefore `composer`, which is itself a
# php-shebang script) resolves through a CloudLinux selector symlink
# (/usr/bin/php -> /etc/cl.selector/php-cli) that stays on PHP 7.2
# regardless of the per-domain PHP-FPM version configured in hPanel. Laravel
# requires >=8.2, so resolve a compatible interpreter directly from the
# CloudLinux alt-php installs instead of depending on that selector.
PHP_BIN=""
for v in 85 84 83 82; do
  candidate="/opt/alt/php${v}/usr/bin/php"
  if [ -x "$candidate" ]; then
    PHP_BIN="$candidate"
    break
  fi
done
if [ -z "$PHP_BIN" ]; then
  echo "No PHP >=8.2 binary found under /opt/alt/phpXX/usr/bin/php" >&2
  exit 1
fi
echo "Using PHP: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

COMPOSER_BIN="$(command -v composer || true)"
if [ -z "$COMPOSER_BIN" ]; then
  echo "composer not found on PATH" >&2
  exit 1
fi
composer() { "$PHP_BIN" "$COMPOSER_BIN" "$@"; }
artisan() { "$PHP_BIN" artisan "$@"; }

composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
  if [ -z "${SETUP_TOKEN:-}" ]; then
    echo "No .env in $BACKEND_DIR yet, and no SETUP_TOKEN was passed in to create one."
    echo "SSH in once and run:"
    echo "  cp .env.example .env && php artisan key:generate --force"
    echo "then set SETUP_TOKEN in .env, and re-run this deploy."
    exit 1
  fi
  cp .env.example .env
  artisan key:generate --force
  sed -i "s|^SETUP_TOKEN=.*|SETUP_TOKEN=${SETUP_TOKEN}|" .env
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# public_html is a one-way copy of backend/public (see below), not a symlink to
# it, so uploaded files (product photos, etc.) must be written directly into
# a directory that's actually served. Point the 'uploads' disk (see
# config/filesystems.php) at public_html/uploads instead of the usual
# storage/app/public + /storage symlink.
mkdir -p "$PUBLIC_DIR/uploads"
chmod -R 775 "$PUBLIC_DIR/uploads"
if ! grep -q '^UPLOADS_PATH=' .env; then
  APP_URL_VALUE=$(grep -E '^APP_URL=' .env | head -1 | cut -d'=' -f2- | tr -d '"')
  {
    echo "UPLOADS_PATH=${PUBLIC_DIR}/uploads"
    echo "UPLOADS_URL=${APP_URL_VALUE}/uploads"
  } >> .env
fi

cp -r public/. "$PUBLIC_DIR"/
sed -i "s|__DIR__\.'/\.\./|__DIR__.'/../app/backend/|g" "$PUBLIC_DIR/index.php"

if [ -f storage/app/installed.lock ]; then
  artisan migrate --force
  artisan config:cache
  artisan route:cache
else
  artisan config:clear
  artisan route:clear
  echo "Not installed yet — visit https://hardwarecenters.com/setup to finish installation."
fi
