#!/usr/bin/env bash
#
# ArtaClean — one-shot server provisioning for a fresh Ubuntu 24.04 host.
#
# Installs PHP 8.4-FPM, Nginx, MySQL 8, Redis, Composer and Node 22, then
# configures the application, the queue worker and the scheduler.
#
#   sudo DOMAIN=artaclean.ir bash deploy/provision.sh
#
# Safe to re-run: it never overwrites an existing .env, never regenerates
# APP_KEY, and never drops a database. Re-running after a `git pull` is a
# perfectly good way to reconcile a drifted server — though deploy/deploy.sh is
# the lighter tool for routine releases.
#
set -euo pipefail

# ---------------------------------------------------------------------------
# Settings (override by exporting before the call)
# ---------------------------------------------------------------------------
DOMAIN="${DOMAIN:-artaclean.ir}"
APP_ROOT="${APP_ROOT:-/var/www/artaclean/current}"
DB_NAME="${DB_NAME:-artaclean}"
DB_USER="${DB_USER:-artaclean}"
PHP_VERSION="8.4"
NODE_MAJOR="22"
RUN_USER="www-data"

# Seeding writes demo products and a default admin password, so it is opt-in.
SEED="${SEED:-false}"

log()  { printf '\n\033[1;34m==>\033[0m \033[1m%s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m [!] %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m [x] %s\033[0m\n' "$*" >&2; exit 1; }

# ---------------------------------------------------------------------------
# Preflight
# ---------------------------------------------------------------------------
[[ $EUID -eq 0 ]] || die "Run this with sudo."

if ! grep -q 'Ubuntu' /etc/os-release 2>/dev/null; then
    warn "This script targets Ubuntu 24.04. Continuing anyway."
fi

[[ -f "${APP_ROOT}/artisan" ]] || die "No Laravel app at ${APP_ROOT}. Clone the repo there first."

cd "$APP_ROOT"

export DEBIAN_FRONTEND=noninteractive

# ---------------------------------------------------------------------------
# Packages
# ---------------------------------------------------------------------------
log "Installing base packages"
apt-get update -qq
apt-get install -y -qq software-properties-common curl git unzip ca-certificates gnupg lsb-release

log "Adding the PHP ${PHP_VERSION} repository"
# Ubuntu 24.04 ships PHP 8.3; 8.4 comes from ondrej/php.
if ! grep -Rq 'ondrej/php' /etc/apt/sources.list.d/ 2>/dev/null; then
    add-apt-repository -y ppa:ondrej/php
    apt-get update -qq
fi

log "Installing PHP ${PHP_VERSION}, Nginx, MySQL and Redis"
apt-get install -y -qq \
    "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-mysql" \
    "php${PHP_VERSION}-redis" "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-xml" \
    "php${PHP_VERSION}-curl" "php${PHP_VERSION}-zip" "php${PHP_VERSION}-intl" \
    "php${PHP_VERSION}-gd" "php${PHP_VERSION}-bcmath" "php${PHP_VERSION}-sqlite3" \
    "php${PHP_VERSION}-opcache" \
    nginx mysql-server redis-server certbot python3-certbot-nginx

log "Installing Node ${NODE_MAJOR}"
if ! command -v node >/dev/null || [[ "$(node -v)" != v${NODE_MAJOR}* ]]; then
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash -
    apt-get install -y -qq nodejs
fi

log "Installing Composer"
if ! command -v composer >/dev/null; then
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm -f /tmp/composer-setup.php
fi

# ---------------------------------------------------------------------------
# PHP tuning
# ---------------------------------------------------------------------------
log "Tuning PHP for production"
cat > "/etc/php/${PHP_VERSION}/fpm/conf.d/99-artaclean.ini" <<'INI'
; Product videos and catalog PDFs
upload_max_filesize = 64M
post_max_size = 68M
max_execution_time = 120
memory_limit = 512M

expose_php = Off

; OPcache: the single biggest win for Core Web Vitals on a PHP host.
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
; Files never change under a running FPM in this deploy model; deploy.sh
; reloads FPM, which flushes the cache.
opcache.validate_timestamps = 0
INI

# The CLI must revalidate, otherwise artisan runs stale code after a deploy.
cat > "/etc/php/${PHP_VERSION}/cli/conf.d/99-artaclean.ini" <<'INI'
memory_limit = -1
opcache.enable_cli = 0
INI

# ---------------------------------------------------------------------------
# Database
# ---------------------------------------------------------------------------
log "Configuring MySQL"
systemctl enable --now mysql

# Reuse the password already in .env so re-running never locks the app out.
DB_PASS=""
if [[ -f .env ]]; then
    DB_PASS="$(grep -E '^DB_PASSWORD=' .env | head -1 | cut -d= -f2- || true)"
    # Strip only the surrounding quotes, not every quote in the value.
    DB_PASS="${DB_PASS%\"}"
    DB_PASS="${DB_PASS#\"}"
fi

# Generated passwords stay alphanumeric so they survive .env, the MySQL client
# and the shell without quoting games.
[[ -n "$DB_PASS" ]] || DB_PASS="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 32)"

mysql --protocol=socket -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

log "Configuring Redis"
# Redis holds cache, sessions and queues; evicting session keys under memory
# pressure would sign users out, so `noeviction` is the correct policy here.
sed -i 's/^# *maxmemory-policy .*/maxmemory-policy noeviction/' /etc/redis/redis.conf || true
systemctl enable --now redis-server
systemctl restart redis-server

# ---------------------------------------------------------------------------
# Application
# ---------------------------------------------------------------------------
log "Writing .env"
if [[ ! -f .env ]]; then
    cp .env.example .env
    NEW_ENV=true
else
    NEW_ENV=false
    warn ".env already exists — leaving its contents alone apart from the DB credentials."
fi

# Values are passed through the environment rather than interpolated into a
# sed or awk expression: a password containing & | \ or / would otherwise be
# silently mangled by the replacement syntax.
set_env() {
    local key="$1" value="$2"

    if grep -qE "^${key}=" .env; then
        AC_KEY="$key" AC_VALUE="$value" awk '
            BEGIN { key = ENVIRON["AC_KEY"]; value = ENVIRON["AC_VALUE"] }
            !replaced && index($0, key "=") == 1 { print key "=" value; replaced = 1; next }
            { print }
        ' .env > .env.new
        cat .env.new > .env
        rm -f .env.new
    else
        printf '%s=%s\n' "$key" "$value" >> .env
    fi
}

set_env DB_CONNECTION mysql
set_env DB_HOST 127.0.0.1
set_env DB_PORT 3306
set_env DB_DATABASE "$DB_NAME"
set_env DB_USERNAME "$DB_USER"
set_env DB_PASSWORD "\"${DB_PASS}\""

if [[ "$NEW_ENV" == true ]]; then
    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_URL "https://${DOMAIN}"
fi

log "Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Only ever generated once; regenerating would invalidate every session and
# every encrypted column.
if ! grep -qE '^APP_KEY=base64:' .env; then
    log "Generating APP_KEY"
    php artisan key:generate --force
fi

log "Building front-end assets"
npm ci --no-audit --no-fund
npm run build

log "Running migrations"
php artisan migrate --force

if [[ "$SEED" == "true" ]]; then
    log "Seeding demo catalog"
    php artisan db:seed --force
fi

log "Linking storage"
php artisan storage:link || true

log "Caching framework state"
php artisan optimize
php artisan filament:optimize

# ---------------------------------------------------------------------------
# Permissions
# ---------------------------------------------------------------------------
log "Setting permissions"
mkdir -p /var/log/artaclean
chown -R "${RUN_USER}:${RUN_USER}" /var/log/artaclean

chown -R "${RUN_USER}:${RUN_USER}" "$APP_ROOT"
# Only these two trees need to be writable by the web user.
chmod -R ug+rwX "${APP_ROOT}/storage" "${APP_ROOT}/bootstrap/cache"
chmod 640 "${APP_ROOT}/.env"

# ---------------------------------------------------------------------------
# Services
# ---------------------------------------------------------------------------
log "Installing systemd units"
for unit in horizon scheduler; do
    sed -e "s|/var/www/artaclean/current|${APP_ROOT}|g" \
        -e "s|/usr/bin/php8.4|/usr/bin/php${PHP_VERSION}|g" \
        "deploy/${unit}.service" > "/etc/systemd/system/artaclean-${unit}.service"
done

systemctl daemon-reload
systemctl enable --now artaclean-horizon artaclean-scheduler
systemctl restart artaclean-horizon artaclean-scheduler

log "Configuring Nginx"
sed -e "s|__DOMAIN__|${DOMAIN}|g" \
    -e "s|__APP_ROOT__|${APP_ROOT}|g" \
    -e "s|php8.4-fpm.sock|php${PHP_VERSION}-fpm.sock|g" \
    deploy/nginx.conf > /etc/nginx/sites-available/artaclean

ln -sf /etc/nginx/sites-available/artaclean /etc/nginx/sites-enabled/artaclean
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl enable --now nginx
systemctl reload nginx
systemctl restart "php${PHP_VERSION}-fpm"

# ---------------------------------------------------------------------------
# Done
# ---------------------------------------------------------------------------
cat <<EOF

$(printf '\033[1;32m')  Provisioning complete.$(printf '\033[0m')

  Site        http://${DOMAIN}
  Admin       http://${DOMAIN}/admin
  Dealers     http://${DOMAIN}/dealer

  Database    ${DB_NAME} / ${DB_USER}  (password stored in ${APP_ROOT}/.env)

  Services    systemctl status artaclean-horizon artaclean-scheduler

  Next steps
    1. Point ${DOMAIN} at this server, then enable HTTPS:
         sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}
       Certbot rewrites the vhost in place and sets up auto-renewal.

    2. Create the first administrator:
         cd ${APP_ROOT} && php artisan tinker
         >>> \$u = App\\Models\\User::create(['name' => 'Admin', 'email' => 'you@example.com', 'password' => 'CHANGE-ME', 'is_active' => true]);
         >>> \$u->roles()->attach(App\\Models\\Role::where('slug', 'admin')->value('id'));

    3. Fill in the storefront details in .env — SITE_PHONE, SITE_WHATSAPP,
       SITE_EMAIL, SITE_ADDRESS — then run: php artisan config:cache

    4. Routine releases from here on use deploy/deploy.sh, not this script.

EOF

if [[ "$SEED" != "true" ]]; then
    warn "Demo catalog not installed. Re-run with SEED=true to load 17 sample machines."
fi
