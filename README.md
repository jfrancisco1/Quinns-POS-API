# Quinn's Laundry POS — Backend API

REST API for Quinn's Laundry Point of Sale system.

**Stack:** Laravel 10 · PHP 8.4 · PostgreSQL · Nginx · Docker · Railway

---

## Getting Started

```bash
git clone <repo-url>
cd laundry-pos-api
cp .env.example .env

composer install
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

**With Docker**

```bash
docker build -t laundry-pos-api .
docker run -p 80:80 --env-file .env laundry-pos-api
```

---

## API Docs

Available at the root URL: `https://laundryappapi-production.up.railway.app/docs`
