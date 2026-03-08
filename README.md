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

## API Reference

**Base URL:** `https://laundryappapi-production.up.railway.app/api/v1`
**Format:** `application/json`

| Status Code | Meaning |
|-------------|---------|
| `200` | Success |
| `201` | Created |
| `404` | Not found |
| `422` | Validation error |
| `500` | Server error |

---

### Customers

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customers` | List all customers |
| POST | `/customers` | Create a customer |
| GET | `/customers/{id}` | Get a customer |
| PUT | `/customers/{id}` | Update a customer |
| DELETE | `/customers/{id}` | Delete a customer |

**Fields**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | ✅ | Must be unique |
| `phone` | string | ❌ | Must be unique |
| `email` | string | ❌ | Must be unique |
| `address` | string | ❌ | |
| `notes` | string | ❌ | |

**Response**
```json
{
  "id": 1,
  "name": "Maria Santos",
  "phone": "09171234567",
  "email": "maria@email.com",
  "address": "123 Main St",
  "notes": null,
  "created_at": "2024-01-01 00:00:00",
  "updated_at": "2024-01-01 00:00:00"
}
```

---

### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/categories` | List all categories |
| POST | `/categories` | Create a category |
| GET | `/categories/{id}` | Get a category |
| PUT | `/categories/{id}` | Update a category |
| DELETE | `/categories/{id}` | Delete a category |

**Fields**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | ✅ | Must be unique |
| `color` | string | ❌ | e.g. `#FF5733` |
| `is_active` | boolean | ❌ | Defaults to `true` |

**Response**
```json
{
  "id": 1,
  "name": "Dry Cleaning",
  "color": "#FF5733",
  "is_active": true,
  "created_at": "2024-01-01 00:00:00",
  "updated_at": "2024-01-01 00:00:00"
}
```

---

### Items

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/items` | List all items |
| POST | `/items` | Create an item |
| GET | `/items/{id}` | Get an item |
| PUT | `/items/{id}` | Update an item |
| DELETE | `/items/{id}` | Delete an item |

**Fields**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | ✅ | |
| `price` | decimal | ✅ | Selling price |
| `cost` | decimal | ✅ | Cost price |
| `description` | string | ❌ | |
| `image` | string | ❌ | Image path or URL |
| `is_active` | boolean | ❌ | Defaults to `true` |
| `category_id` | integer | ❌ | Must exist in categories |

**Response**
```json
{
  "id": 1,
  "name": "Polo Shirt",
  "description": "Regular wash and press",
  "image": null,
  "price": "50.00",
  "cost": "20.00",
  "is_active": true,
  "category": {
    "id": 1,
    "name": "Dry Cleaning",
    "color": "#FF5733",
    "is_active": true
  },
  "created_at": "2024-01-01 00:00:00",
  "updated_at": "2024-01-01 00:00:00"
}
```
