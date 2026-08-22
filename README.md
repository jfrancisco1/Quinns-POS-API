# Quinn's Laundry POS — Backend API

REST API for Quinn's Laundry Point of Sale system.

**Stack:** Laravel 10 · PHP 8.4 · PostgreSQL · Nginx · Docker · Railway

---

## Getting Started

### Option A — Local PHP

Requires PHP 8.4, Composer, and a local PostgreSQL instance (this app relies on Postgres-specific SQL).

```bash
git clone <repo-url>
cd laundry-pos-api
cp .env.example .env
# then edit .env: set DB_CONNECTION=pgsql and point DB_* at your local Postgres

composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

### Option B — Docker (recommended, no local PHP/Postgres install needed)

This runs the app via [Laravel Sail](https://laravel.com/docs/10.x/sail)'s runtime image (already a dev dependency) against a containerized Postgres, with your code bind-mounted live — edits on disk are picked up immediately, no rebuild required.

**First-time setup:**
```bash
cp .env.example .env
```
`.env.example` is already configured with the Docker/Postgres values below — no manual edits needed for this path:
```
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=quinns_pos
DB_USERNAME=sail
DB_PASSWORD=password

APP_PORT=8000
WWWUSER=1000
WWWGROUP=1000
```

Then, from the project root:
```bash
# 1. bootstrap: install composer deps via the official composer-only image
#    (needed once — the app image below builds from vendor/laravel/sail, which doesn't exist until this runs)
docker run --rm -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install

# 2. build the full app image (PHP 8.4 + extensions + Node) — several minutes, first time only
npm run docker:build

# 3. generate the app key, then start the stack
docker compose run --rm laravel.test php artisan key:generate
npm run docker:up        # starts the app (localhost:8000) + Postgres (localhost:5432)
npm run docker:migrate
npm run docker:seed -- --class=SampleDataSeeder   # optional: demo tenant/branch/users/data
```

**Day-to-day** (containers already built):
```bash
npm run docker:up        # start
npm run docker:down      # stop and remove containers (DB data persists in a named volume)
npm run docker:logs      # tail app logs
npm run docker:artisan -- migrate         # run any artisan command
npm run docker:artisan -- tinker
npm run docker:shell     # shell into the app container
```

**Inspecting the database:**
```bash
npm run docker:psql      # opens a psql prompt (\dt to list tables, \d expenses to describe one)
```
Or point a GUI client (TablePlus, DBeaver, pgAdmin, VS Code's PostgreSQL extension) at `localhost:5432`, database `quinns_pos`, user `sail`, password `password` (same values as in `.env`).

Docker Desktop's own UI also shows the two containers (`laravel.test`, `pgsql`) if you'd rather start/stop/view logs by clicking instead of the CLI.

**Building/deploying the production image** (Railway) is a separate concern from local dev — see `Dockerfile` at the repo root; it copies code in at build time and isn't meant for iterative development.

---

## API Docs

Available at the root URL: `https://laundryappapi-production.up.railway.app/docs`
