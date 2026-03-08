# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install PHP dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Feature/SomeTest.php

# Run tests with a filter
vendor/bin/phpunit --filter test_method_name

# Code style (Laravel Pint)
vendor/bin/pint

# Database migrations
php artisan migrate

# Run dev server
php artisan serve
```

## Architecture

Laravel 10 REST API for a laundry POS system with multi-tenancy support.

**Request flow:** `routes/api.php` → Controller → Service → Model → Database

**Multi-tenancy:** Every resource belongs to a `tenant_id` and optionally a `branch_id`. `BaseService` automatically scopes all queries based on the authenticated user's role:
- `admin` — sees all records for their tenant
- `staff` / `delivery` — scoped to their branch only

**Service layer** (`app/Services/`): Business logic lives here, not in controllers. `BaseService` provides `tenantScope()` and `authorizeTenant()`. All services extend it.

**API prefix:** All routes are under `/api/v1`.

**Authentication:** Laravel Sanctum (token-based).

**Deployment:** Docker (PHP 8.4-FPM + Nginx), PostgreSQL in production (Railway). Dockerfile runs migrations on startup.

## Database Schema

Key tables: `tenants`, `branches`, `users`, `customers`, `orders`, `order_items`, `categories`, `items`, `discounts`, `expenses`. Customers use soft deletes.
