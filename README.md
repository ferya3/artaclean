# ArtaClean — Industrial Cleaning Equipment Storefront

A Laravel storefront for industrial cleaning equipment: scrubber dryers, sweepers,
industrial vacuums, polishers, pressure washers and steam cleaners.

The buyer here is a procurement manager or facilities lead, not a consumer. They
arrive wanting **specifications, comparison, a sizing answer and a price** — so the
site is built around those four things rather than around a shopping cart.

---

## What makes this different from a generic catalog

| Capability | What it does |
|---|---|
| **Machine selector** | Turns floor area + shift length + environment into a required productivity figure, a machine class and a shortlist. Uses environment-specific efficiency factors, not the optimistic catalog number. |
| **Side-by-side comparison** | Up to four machines, with a "differences only" toggle and a marker on the winning value in each row (more suction wins, less noise wins). |
| **Professional filters** | Brand, power source, operator type, tank capacity, motor power, cleaning width — plus **coverage area in m²**, which is how buyers actually think. Facet counts recompute as filters change. |
| **Price on request** | Iranian equipment prices move weekly, so numbers stay hidden behind a "استعلام قیمت روز" flow by default. Flip `SITE_PRICES_PUBLIC` to show them. |
| **Environment landing pages** | Factory, warehouse, hospital, mall, hotel, car park, airport, car wash — each with its own challenges, FAQ and machine list. |
| **Rental** | Daily / weekly / monthly plans per machine, with rental enquiries flowing into the same quote pipeline. |
| **Lead scoring CRM** | Every form lands in one pipeline, scored 0–100 by source, business type, company and machine value. Repeat enquiries from one phone number enrich the existing lead instead of fragmenting it. |
| **Dealer panel** | Each dealership signs in to a separate panel scoped by a global query scope applied in middleware — not by hiding buttons. |
| **SEO** | Root-level keyword URLs, Product / FAQPage / BreadcrumbList / Organization / WebSite JSON-LD, hreflang, a generated sitemap, and per-product FAQs. |

---

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 |
| Interactivity | Livewire 4 + Alpine (bundled with Livewire) |
| Styling | Tailwind CSS 4 |
| Sliders | Swiper.js |
| Admin | **Filament 5** |
| Database | MySQL 8 (SQLite for local/testing) |
| Cache / session / queue | Redis |
| Queue dashboard | Laravel Horizon |
| Search | Laravel Scout (`database` driver by default, Meilisearch in production) |
| Server | Ubuntu 24.04 · Nginx · PHP 8.4 · Cloudflare · S3-compatible object storage |

> **A note on Filament.** The brief asked for Filament 4. Filament 4 requires
> Livewire 3 and Laravel 11/12, so it cannot be installed alongside Laravel 13 +
> Livewire 4. Filament 5 is the release built for this stack, and that is what is
> installed. Everything else in the brief is on the requested version.

---

## Architecture

```
app/
├── Contracts/Repositories/     Repository interfaces
├── Repositories/Eloquent/      Eloquent implementations, bound in a provider
├── Services/                   Service layer
│   ├── MachineSelectorService  the sizing model
│   ├── ComparisonService       shortlist + comparison table
│   ├── LeadScoringService      0–100 lead score
│   ├── SchemaBuilder           JSON-LD
│   ├── SeoService              per-request meta bag
│   └── NavigationService       cached menus
├── CQRS/
│   ├── Bus/                    CommandBus (transactional) · QueryBus (cacheable)
│   ├── Commands/ Queries/      readonly DTOs
│   └── Handlers/               one handler per message
├── Livewire/                   catalog, selector, quote form, comparison
├── Filament/                   admin panel resources and widgets
├── Filament/Dealer/            dealer panel
└── Support/HasTranslations     JSON per-locale attributes
```

**Repository pattern** keeps query construction out of controllers and Livewire
components. **CQRS** separates writes (transactional, via `CommandBus`) from reads
(memoisable, via `QueryBus`); a query that implements `CacheableQuery` derives its
own cache key and tags.

### Multi-language

Translatable columns are JSON maps: `{"fa": "…", "en": "…"}`.

* `$product->name` returns the active locale, with fallback.
* `$product->attributesToArray()['name']` returns the whole map — which is what
  the Filament `name.fa` / `name.en` fields bind to.
* Searching goes through the `->` JSON path syntax, because the stored JSON has
  escaped unicode and a plain `LIKE` would never match a Persian term.

Persian is the default and renders RTL; `?lang=en` switches and is remembered.

---

## Local setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Fastest start: SQLite, no services needed.
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/;s/^SESSION_DRIVER=.*/SESSION_DRIVER=file/;s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env
touch database/database.sqlite

php artisan migrate --seed
npm run build
php artisan serve
```

For the full stack, keep `.env.example` as shipped (MySQL 8 + Redis) and run
`php artisan horizon` alongside.

### Windows (cmd)

> **Read this first.** Horizon requires `ext-pcntl` and `ext-posix`, which do not
> exist in PHP for Windows at all. A plain `composer install` therefore fails on
> Windows. Skip those two platform requirements — nothing but the `horizon`
> command itself needs them, and queues run inline locally anyway.

```bat
:: 1. Dependencies
composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
npm install

:: 2. Environment
copy .env.example .env
php artisan key:generate

:: 3. Point the app at SQLite, files and an inline queue
powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' -replace '^CACHE_STORE=.*','CACHE_STORE=file' -replace '^SESSION_DRIVER=.*','SESSION_DRIVER=file' -replace '^QUEUE_CONNECTION=.*','QUEUE_CONNECTION=sync' | Set-Content .env"

:: Comment out the MySQL host settings so they cannot override the above
powershell -Command "(Get-Content .env) -replace '^(DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=)','# $1' | Set-Content .env"

:: 4. Database
type nul > database\database.sqlite
php artisan migrate --seed

:: 5. Build and run
npm run build
php artisan serve
```

Then open <http://127.0.0.1:8000>.

**Windows notes**

* **PHP 8.4+** with `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `intl`,
  `openssl`, `curl`, `zip` and `gd` enabled in `php.ini`. Laragon and Herd ship
  these on; a bare `php.ini-development` does not — uncomment them.
* **Using MySQL instead** (Laragon/XAMPP): skip step 3, create an empty
  `artaclean` database, and set `DB_USERNAME` / `DB_PASSWORD` in `.env` by hand.
* **`php artisan storage:link`** creates a symlink, which Windows only permits
  under Developer Mode or an Administrator prompt. You only need it once uploaded
  images have to be served.
* **`php artisan horizon` will not run on Windows.** With `QUEUE_CONNECTION=sync`
  jobs execute inline, so nothing is lost locally. Production is Ubuntu, where
  Horizon runs under systemd — see `deploy/`.
* **Prefer WSL2** if you want the environment to match production: install
  Ubuntu 24.04 from the Microsoft Store, clone the repo *inside* the Linux
  filesystem (`~/artaclean`, not `/mnt/c/...`, which is dramatically slower), and
  follow the Unix instructions above unchanged.

### Seeded accounts

| Role | Email | Password | Panel |
|---|---|---|---|
| Admin | `admin@artaclean.ir` | `password` | `/admin` |
| Sales | `sales@artaclean.ir` | `password` | `/admin` |
| Dealer | `dealer.tehran@artaclean.ir` | `password` | `/dealer` |

The seed also creates 17 machines with realistic specifications, 9 categories,
8 environments, 7 brands, 3 articles, site-wide FAQs and a demo sales pipeline.

---

## Tests

```bash
php artisan test
```

67 tests covering the sizing model, translations, catalog filters and facets,
the quote/lead pipeline (including de-duplication and scoring), the comparison
table's winner logic, every public route, the SEO output, and panel access
control including dealer scoping.

---

## Deployment

### First install, on a fresh Ubuntu 24.04 server

```bash
sudo apt-get update && sudo apt-get install -y git && \
sudo git clone https://github.com/ferya3/artaclean.git /var/www/artaclean/current && \
sudo DOMAIN=artaclean.ir SEED=true bash /var/www/artaclean/current/deploy/provision.sh
```

That installs PHP 8.4-FPM, Nginx, MySQL 8, Redis, Composer and Node 22; creates
the database with a generated password; installs dependencies; builds assets;
migrates; and brings up Horizon and the scheduler under systemd.

The site is served over plain HTTP at this point, and `APP_URL` is set to
`http://` to match. Once DNS points at the box, turn on TLS — **both commands,
in this order**:

```bash
sudo certbot --nginx -d artaclean.ir -d www.artaclean.ir

cd /var/www/artaclean/current \
  && sudo sed -i 's|^APP_URL=http://|APP_URL=https://|' .env \
  && sudo -u www-data php artisan config:cache
```

Certbot opens port 443; `APP_URL` is what makes the application generate links
to it. Doing only the first leaves the site reachable but still linking to
`http://`; doing only the second points every link and asset at a port that
isn't listening.

Notes:

* **The repository is private**, so the clone will ask for credentials. Use a
  deploy key, or a personal access token in the URL.
* **`SEED=true` loads the demo catalog.** Drop it on a real production install —
  seeding writes 17 sample machines and demo accounts with known passwords.
* **`provision.sh` is safe to re-run.** It never overwrites an existing `.env`,
  never regenerates `APP_KEY` (which would invalidate every session), and never
  drops a database. It reuses the `DB_PASSWORD` already in `.env`.
* **`nginx.conf` is HTTP-only by design.** A fresh server has no certificate, so
  hard-coded `ssl_certificate` paths would fail `nginx -t` and block the reload
  you need to obtain one. Certbot adds the TLS block and the redirect in place.
* **Nginx comes up before Horizon.** The web server is configured first, and the
  queue worker and scheduler are started non-fatally afterwards, so a background
  service that will not start can never leave the box serving nothing.
* **`provision.sh` finishes with a health check** against `/up` and prints the
  three logs to read if it does not answer 200.

### If the site does not come up

Work down this list; each step tells you which layer is at fault.

| Symptom | Check | Usual cause |
|---|---|---|
| Connection times out | `sudo ufw status` | Firewall allows only SSH. `sudo ufw allow 'Nginx Full'` |
| Default "Welcome to nginx" page | `ls -l /etc/nginx/sites-enabled/` | `provision.sh` aborted before the Nginx step — read its last output |
| Page loads but is unstyled, links dead | `grep APP_URL .env` | `APP_URL=https://` while Nginx is still HTTP-only. Set it to `http://` and `php artisan config:cache` |
| 502 Bad Gateway | `systemctl status php8.4-fpm` | FPM down, or the socket path in the vhost does not match |
| 500 on every page | `tail -50 storage/logs/laravel-$(date +%F).log` | Usually `.env` credentials or `storage/` permissions |
| Everything looks right, still nothing | `curl -I -H 'Host: artaclean.ir' http://127.0.0.1/up` | 200 here means the server is fine and the problem is DNS or Cloudflare |

Behind Cloudflare, keep the proxy in **Full (strict)** mode once certbot has
run. *Flexible* mode makes Cloudflare speak HTTP to an origin that redirects to
HTTPS, which is an infinite redirect loop.

### Routine releases

```bash
cd /var/www/artaclean/current && ./deploy/deploy.sh
```

`deploy/` contains the production pieces:

| File | Purpose |
|---|---|
| `provision.sh` | One-shot server bootstrap: packages, database, services, vhost |
| `deploy.sh` | Routine release: pull, install, build, migrate, cache, restart workers |
| `nginx.conf` | Site config with Cloudflare real-IP, security headers, asset caching |
| `horizon.service` | Horizon under systemd |
| `scheduler.service` | `schedule:work` under systemd |

Queue supervisors are split in `config/horizon.php`: `notifications` (a new lead
must reach sales immediately), `search` (Scout re-indexing), and `default`.

### Media and documents

`FILESYSTEM_DISK=s3` points uploads at any S3-compatible store (Arvan Cloud,
Liara, MinIO, AWS). Downloads are always routed through the app rather than
linked directly, so gated catalogs can capture a lead and the counter stays
honest.

### Placeholder assets

`public/images/` ships SVG placeholders and `public/videos/` is empty. Drop a real
`public/videos/hero.mp4` in for the home page hero; the poster image carries the
LCP either way, and the video is `preload="none"`.

---

## Operational notes

* **Cache class allow-list.** Laravel 13 refuses to unserialize classes from the
  cache by default. The navigation tree and home page payload are cached as
  Eloquent collections, so those specific classes are allow-listed in
  `config/cache.php`. Add to that list only when you genuinely cache a new class.
* **Cache invalidation.** Saving a product, category, brand or environment flushes
  the navigation and home page caches through `CatalogCacheObserver`. On Redis this
  uses tags; on file/database stores it falls back to explicit keys.
* **Category slugs are the SEO contract.** They sit at the domain root
  (`/scrubber-dryer`) and products hang off them (`/scrubber-dryer/arta-scrub-70`).
  Reaching a product through the wrong category returns 404 rather than creating a
  duplicate URL. Do not rename a slug after launch without a redirect.
