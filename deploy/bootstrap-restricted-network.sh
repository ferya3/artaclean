#!/usr/bin/env bash
#
# ArtaClean — one-file bootstrap for a server behind a restricted network.
#
# Upload this single file to a fresh Ubuntu 24.04 host and run it:
#
#   sudo DOMAIN=artaclean.ir SEED=true bash bootstrap-restricted-network.sh
#
# It fetches the repository and then hands over to deploy/provision.sh, which
# does the real work. Everything here exists to get provision.sh to the point
# where it can run at all.
#
# Why this file exists
# --------------------
# From some networks — Iranian hosts in particular — two of the services a
# normal Laravel install depends on are unreachable:
#
#   repo.packagist.org      Composer's package metadata
#   codeload.github.com     the zip archives Composer pulls dist files from
#
# while github.com, the Ubuntu mirrors, nodesource and npm all answer fine. A
# plain `composer install` therefore hangs until it times out, and no amount of
# retrying fixes it. Pointing Composer at a full mirror replaces both hosts at
# once: the mirror serves the metadata *and* the dist archives, so codeload is
# never contacted.
#
# The mirror is configured globally, so it also covers every later
# deploy/deploy.sh run on this machine — it is set up once, not per release.
#
set -euo pipefail

# ---------------------------------------------------------------------------
# Settings
# ---------------------------------------------------------------------------
REPO_URL="${REPO_URL:-https://github.com/ferya3/artaclean.git}"
APP_ROOT="${APP_ROOT:-/var/www/artaclean/current}"
DOMAIN="${DOMAIN:-artaclean.ir}"
SEED="${SEED:-false}"

# Candidate Composer mirrors, tried in order. Both carry the full packagist
# metadata and the dist archives. Override with COMPOSER_MIRROR to force one.
MIRRORS=(
    "https://mirrors.aliyun.com/composer/"
    "https://mirrors.cloud.tencent.com/composer/"
)

# How long to wait for another apt process (cloud-init, unattended-upgrades)
# to release the dpkg lock before giving up and saying so.
APT_LOCK_WAIT="${APT_LOCK_WAIT:-600}"

log()  { printf '\n\033[1;34m==>\033[0m \033[1m%s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m [!] %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m [x] %s\033[0m\n' "$*" >&2; exit 1; }

[[ $EUID -eq 0 ]] || die "Run this with sudo."

# ---------------------------------------------------------------------------
# Wait out the automatic updater
#
# A freshly provisioned cloud image almost always has unattended-upgrades
# holding the dpkg lock for the first few minutes. Killing it risks a
# half-configured package, so wait instead — and say what is being waited on,
# because a silent hang looks like a crash.
# ---------------------------------------------------------------------------
if command -v fuser >/dev/null && fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1; then
    log "Waiting for another apt process to finish"

    waited=0
    while fuser /var/lib/dpkg/lock-frontend >/dev/null 2>&1; do
        if (( waited >= APT_LOCK_WAIT )); then
            warn "Still locked after ${APT_LOCK_WAIT}s. The holder is:"
            fuser -v /var/lib/dpkg/lock-frontend 2>&1 | sed 's/^/      /' || true
            die "Stop it with: systemctl stop unattended-upgrades — then re-run this script."
        fi

        printf '\r    %ds…' "$waited"
        sleep 5
        waited=$(( waited + 5 ))
    done

    printf '\r    lock released after %ds\n' "$waited"
fi

# ---------------------------------------------------------------------------
# Git and the repository
# ---------------------------------------------------------------------------
export DEBIAN_FRONTEND=noninteractive

log "Installing git"
apt-get update -qq
apt-get install -y -qq git curl ca-certificates

if [[ -d "${APP_ROOT}/.git" ]]; then
    log "Repository already present — fetching the latest commit"
    git config --global --add safe.directory "$APP_ROOT" 2>/dev/null || true
    git -C "$APP_ROOT" pull --ff-only || warn "Could not fast-forward; continuing with what is on disk."
else
    log "Cloning ${REPO_URL}"
    mkdir -p "$(dirname "$APP_ROOT")"

    # A shallow clone is a fraction of the transfer, which matters a great deal
    # on a link that is already the weak point of this install.
    git clone --depth 1 "$REPO_URL" "$APP_ROOT"
    git config --global --add safe.directory "$APP_ROOT" 2>/dev/null || true
fi

[[ -f "${APP_ROOT}/deploy/provision.sh" ]] || die "The clone is missing deploy/provision.sh."

# ---------------------------------------------------------------------------
# Composer
# ---------------------------------------------------------------------------
if ! command -v composer >/dev/null; then
    log "Installing Composer"

    # provision.sh installs php8.4-cli later, but Composer's installer needs a
    # PHP binary right now; the distribution's own PHP is enough for this.
    command -v php >/dev/null || apt-get install -y -qq php-cli php-mbstring php-xml

    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    rm -f /tmp/composer-setup.php
fi

log "Selecting a reachable Composer mirror"

CHOSEN="${COMPOSER_MIRROR:-}"

if [[ -z "$CHOSEN" ]]; then
    for candidate in "${MIRRORS[@]}"; do
        printf '    testing %s ' "$candidate"

        if curl -sS -o /dev/null --max-time 10 "${candidate}packages.json" 2>/dev/null; then
            printf 'reachable\n'
            CHOSEN="$candidate"
            break
        fi

        printf 'unreachable\n'
    done
fi

if [[ -n "$CHOSEN" ]]; then
    # Global, so every later `composer install` — including the ones
    # deploy/deploy.sh runs — goes through the mirror too.
    composer config -g repos.packagist composer "$CHOSEN"
    printf '\033[1;32m    using %s\033[0m\n' "$CHOSEN"
else
    warn "No mirror answered. Falling back to packagist.org, which may time out."
    warn "If it does, re-run with: COMPOSER_MIRROR=https://your-mirror/ bash $0"
fi

# ---------------------------------------------------------------------------
# Hand over
# ---------------------------------------------------------------------------
log "Starting provisioning"
cd "$APP_ROOT"
exec env DOMAIN="$DOMAIN" SEED="$SEED" APP_ROOT="$APP_ROOT" \
    bash "${APP_ROOT}/deploy/provision.sh"
