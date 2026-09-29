#!/bin/bash
# Prepares a Claude Code on the web session: a working Laravel install, and the
# skill set this project is worked on with.
#
# Local sessions are skipped - a developer's own machine has its own global
# skills, its own .env and its own database, and this should not touch any of
# them.
set -euo pipefail

if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

cd "${CLAUDE_PROJECT_DIR:-$(dirname "$0")/../..}"

# Laravel keeps these out of git with a .gitignore inside each; mkdir is a
# no-op when they are present. Before composer, whose post-autoload-dump hook
# runs artisan (package:discover, filament:upgrade) and writes bootstrap/cache.
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/testing \
         storage/logs \
         bootstrap/cache

# --- Environment ----------------------------------------------------------
# .env.example ships the production stack: MySQL 8 + Redis + Horizon. Neither
# service exists in this container, so a fresh .env is switched to the
# no-services combination the README calls the fastest start - sqlite, file
# cache and sessions, synchronous queue.
#
# Only on creation: if a .env is already here, it is one this session made and
# possibly edited, and clobbering it would discard that.
# DB_DATABASE is rewritten alongside DB_CONNECTION, and that matters: the
# sqlite connection is env('DB_DATABASE', database_path('database.sqlite')), so
# the MySQL database *name* shipped in .env.example is read as a *file path*
# the moment the driver becomes sqlite. Switching only DB_CONNECTION - which is
# what the README's local-setup block does - leaves DB_DATABASE=artaclean, and
# the database is silently written to ./artaclean in the repository root while
# the database/database.sqlite you were told to create stays empty and unused.
if [ ! -f .env ]; then
  cp .env.example .env
  sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/;
          s#^DB_DATABASE=.*#DB_DATABASE=database/database.sqlite#' .env
  sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/;
          s/^SESSION_DRIVER=.*/SESSION_DRIVER=file/;
          s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env
fi

# --- PHP dependencies -----------------------------------------------------
echo "Installing composer dependencies..."
composer install --no-interaction --no-progress --quiet

grep -q '^APP_KEY=base64:' .env || php artisan key:generate --no-interaction --force

# --- Database -------------------------------------------------------------
# Seed only on first creation: seeding a populated database again would
# duplicate the demo products, content and pipeline rows.
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
  php artisan migrate --seed --no-interaction --force
else
  php artisan migrate --no-interaction --force
fi

php artisan storage:link --no-interaction

# --- Frontend assets ------------------------------------------------------
# Vazirmatn is bundled through npm rather than fetched from a font CDN, so
# unlike some Laravel setups this build needs no network at build time.
echo "Installing npm dependencies and building assets..."
npm install --no-audit --no-fund --silent
npm run build --silent

# --- Skills ---------------------------------------------------------------
# Installed globally (-g), into ~/.claude/skills, rather than committed to the
# repo: the container is fresh each session, so this costs a download instead
# of megabytes of vendored files in git history.
#
# Everything above this line is what the app needs to boot; everything below is
# tooling for whoever is working on it. So the two are separated:
#
#   - The whole block is skipped by exporting SKIP_SKILLS=true. On a network
#     where npm or GitHub is slow or filtered — which is where this project is
#     deployed — six npx fetches at the start of every session is a toll worth
#     being able to decline.
#   - A failure here never aborts the hook. Under `set -e` a single unreachable
#     repository would have taken the session down with it *after* the install
#     had already succeeded, which reads as a broken setup when in fact the app
#     is ready and only the extras are missing.
if [ "${SKIP_SKILLS:-false}" = "true" ]; then
  echo "Session ready (skills skipped)."
  exit 0
fi

echo "Installing skills..."

skills_failed=0
try_skills() {
  "$@" || { skills_failed=1; echo "  ...skipped: $* (not reachable)"; }
}

try_skills npx --yes skills@latest add obra/superpowers -g -a claude-code --copy -y \
  -s brainstorming -s writing-plans -s executing-plans \
  -s subagent-driven-development -s dispatching-parallel-agents \
  -s test-driven-development -s systematic-debugging \
  -s requesting-code-review -s receiving-code-review \
  -s verification-before-completion -s finishing-a-development-branch \
  -s using-git-worktrees -s using-superpowers -s writing-skills

try_skills npx --yes skills@latest add thedotmack/claude-mem -g -a claude-code --copy -y \
  -s smart-explore -s learn-codebase -s pathfinder -s babysit

try_skills npx --yes skills@latest add pbakaus/impeccable -g -a claude-code --copy -y -s impeccable
try_skills npx --yes skills@latest add vercel-labs/skills -g -a claude-code --copy -y -s find-skills
try_skills npx --yes skills@latest add rebelytics/one-skill-to-rule-them-all -g -a claude-code --copy -y \
  -s task-observer

# UI/UX Pro Max ships as its own CLI rather than a skills-repo, so it installs
# in two steps. -g here means the home directory, same destination as above.
try_skills npm install -g --silent ui-ux-pro-max-cli@latest
try_skills uipro init -g -a claude --force

if [ "$skills_failed" = "1" ]; then
  echo "Session ready (some skills were not reachable)."
else
  echo "Session ready."
fi
