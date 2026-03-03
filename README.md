# Quinn's Laundry POS — Backend API

Backend REST API for the Quinn's Laundry Point of Sale system, built with Laravel 10.

---

## Tech Stack

- **Language:** PHP 8.1+
- **Framework:** Laravel 10
- **Authentication:** Laravel Sanctum (token-based)
- **Database:** PostgreSQL
- **Testing:** PHPUnit

---

## Requirements

- PHP >= 8.1
- Composer
- PostgreSQL 14+

---

## Getting Started

```bash
# Clone the repository
git clone <repo-url>
cd laundry-pos-api

# Install PHP dependencies
composer install

# Copy environment file and configure
cp .env.example .env

# Generate application key
php artisan key:generate
```

---

## Environment Variables

Copy `.env.example` to `.env` and update the following:

```env
APP_NAME="Quinns Laundry POS"
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laundry_pos
DB_USERNAME=postgres
DB_PASSWORD=your_db_password
```

> On Railway, these variables are automatically injected from the Railway dashboard.

---

## Database Setup

### Local (WSL / Linux)

```bash
# Start PostgreSQL
sudo service postgresql start

# Create the database
sudo -u postgres psql -c "CREATE DATABASE laundry_pos;"
```

### Run Migrations

```bash
# Run all migrations
php artisan migrate

# Rollback last migration batch
php artisan migrate:rollback

# Fresh migration (drops all tables and re-runs)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

---

## Running the Server

```bash
# WSL users — use 0.0.0.0 to allow Windows access
php artisan serve --host=0.0.0.0 --port=8000
```

---

## API Reference

All endpoints are prefixed with `/api/v1`.

---

### Customers

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/customers` | List all customers | Yes |
| POST | `/api/v1/customers` | Create a new customer | Yes |
| GET | `/api/v1/customers/{id}` | Get a single customer | Yes |
| PUT | `/api/v1/customers/{id}` | Update a customer | Yes |
| DELETE | `/api/v1/customers/{id}` | Soft delete a customer | Yes |


