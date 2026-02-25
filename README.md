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
- Node.js & npm (for asset compilation)

---

## Getting Started

```bash
# Clone the repository
git clone <repo-url>
cd laundry-pos-api

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file and configure
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Start the development server
php artisan serve
```

---

## Environment Variables

Copy `.env.example` to `.env` and update the following:

```env
APP_NAME="Quinns Laundry POS"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry_pos
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

## Authentication

This API uses [Laravel Sanctum](https://laravel.com/docs/sanctum) for token-based authentication.

Include the token in the `Authorization` header for protected routes:

```
Authorization: Bearer <your-token>
```

---

## Testing

```bash
php artisan test
```

---

## License

Private — Quinn's Laundry. All rights reserved.
