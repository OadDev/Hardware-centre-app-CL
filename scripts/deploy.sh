#!/usr/bin/env bash
# Runs ON THE SERVER (invoked over SSH by .github/workflows/deploy.yml) after the
# repo has been cloned/updated at $APP_DIR. Never touches DB credentials or the
# admin account — those are only ever entered by hand through /setup.
set -euo pipefail

APP_DIR="$HOME/domains/hardwarecenters.com/app"
PUBLIC_DIR="$HOME/domains/hardwarecenters.com/public_html"
BACKEND_DIR="$APP_DIR/backend"

cd "$BACKEND_DIR"

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
  php artisan key:generate --force
  sed -i "s|^SETUP_TOKEN=.*|SETUP_TOKEN=${SETUP_TOKEN}|" .env
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

cp -r public/. "$PUBLIC_DIR"/
sed -i "s|__DIR__\.'/\.\./|__DIR__.'/../app/backend/|g" "$PUBLIC_DIR/index.php"

if [ -f storage/app/installed.lock ]; then
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
else
  php artisan config:clear
  php artisan route:clear
  echo "Not installed yet — visit https://hardwarecenters.com/setup to finish installation."
fi
