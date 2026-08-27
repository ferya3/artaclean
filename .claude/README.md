# Claude Code session setup

`hooks/session-start.sh` runs once at the start of every Claude Code on the web
session, registered through `settings.json`. A web session starts from an empty
container, so without it there is no `vendor/`, no `.env`, no database and no
`node_modules` - `php artisan serve` cannot boot at all.

The hook does, in order:

1. Creates Laravel's runtime directories - before composer, whose
   post-autoload-dump hook runs artisan (`package:discover`,
   `filament:upgrade`) and writes into `bootstrap/cache`.
2. Creates `.env` from `.env.example`, switched to the no-services stack
   (see below).
3. `composer install`, then `php artisan key:generate` if `APP_KEY` is empty.
4. Migrates. Seeds **only** on first creation of the database - re-seeding a
   populated database would duplicate the demo products, content and pipeline.
5. `php artisan storage:link`.
6. `npm install` and `npm run build`. Vazirmatn is bundled through npm rather
   than fetched from a font CDN, so this build needs no network access.
7. Installs 28 skills globally into `~/.claude/skills`, rather than committed
   here - the container is fresh each session, so this costs a download
   instead of megabytes of vendored files in git history.

Local sessions exit immediately (`CLAUDE_CODE_REMOTE` guard) - a developer's
own machine has its own global skills, `.env` and database.

## Why the .env is rewritten

`.env.example` ships the production stack: MySQL 8, Redis, Horizon. None of
those services run in the container, so a fresh `.env` is switched to sqlite,
file cache and sessions, and a synchronous queue - the combination the README
calls the fastest start.

`DB_DATABASE` is rewritten along with `DB_CONNECTION`, and that part is not
optional. The sqlite connection is defined as
`env('DB_DATABASE', database_path('database.sqlite'))`, so the MySQL database
*name* in `.env.example` is read as a *file path* the moment the driver becomes
sqlite. Switching only `DB_CONNECTION` - which is what the README's local-setup
block does - leaves `DB_DATABASE=artaclean` and silently writes the database to
`./artaclean` in the repository root, as an untracked 600 KB file, while the
`database/database.sqlite` you were told to create stays empty and unused.
Worth fixing in the README's own instructions too.

## Changing the skill list

Edit the `npx skills add` lines in the hook. `npx skills find <query>` searches
for more; `npx skills remove -g -s <name>` drops one from the current session.

`impeccable` sends a telemetry ping to impeccable.style. Add
`export IMPECCABLE_NO_TELEMETRY=1` to the hook to disable it.
