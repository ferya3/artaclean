#!/usr/bin/env bash
#
# Zero-downtime-ish deploy for a single Ubuntu 24.04 host.
#
#   ./deploy/deploy.sh
#
# Assumes the repo is checked out at /var/www/artaclean/current and that the
# systemd units in this directory are installed.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/artaclean/current}"
PHP="${PHP:-/usr/bin/php8.4}"

cd "$APP_DIR"

echo "==> Pulling changes"
git pull --ff-only

echo "==> Entering maintenance mode"
# `--render` serves a styled page instead of a bare 503; the secret lets the
# team keep browsing the site while the deploy runs.
$PHP artisan down --render="errors::503" --secret="${DEPLOY_SECRET:-deploy}" || true

cleanup() {
    $PHP artisan up || true
}
trap cleanup EXIT

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Building assets"
npm ci
npm run build

echo "==> Running migrations"
$PHP artisan migrate --force

echo "==> Caching framework state"
$PHP artisan optimize          # config, routes, events, views
$PHP artisan filament:optimize # Filament components and Blade icons

echo "==> Linking storage"
$PHP artisan storage:link || true

echo "==> Restarting workers"
# Horizon exits cleanly and systemd brings it back on the new code.
$PHP artisan horizon:terminate || true
sudo systemctl restart artaclean-scheduler || true

echo "==> Reloading PHP-FPM"
sudo systemctl reload php8.4-fpm

echo "==> Deploy complete"
