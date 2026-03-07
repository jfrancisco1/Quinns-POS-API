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

**Status Codes:** `200` Success · `201` Created · `404` Not found · `422` Validation error · `500` Server error

---

### Customers

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customers` | List all customers |
| POST | `/customers` | Create a customer |
| GET | `/customers/{id}` | Get a customer |
| PUT | `/customers/{id}` | Update a customer |
| DELETE | `/customers/{id}` | Soft delete a customer |

**Customer object**
```json
{ "id": 1, "name": "Maria Santos", "phone": "09171234567", "email": "...", "address": "...", "notes": null, "created_at": "...", "updated_at": "..." }
```

**GET** `/customers` — Returns all customers (no pagination).

**POST** `/customers` — Returns `201` with created customer.

| Field | Required |
|-------|----------|
| `name` | ✅ |
| `phone` | ✅ unique |
| `email` | ❌ unique |
| `address` | ❌ |
| `notes` | ❌ |

**GET** `/customers/{id}` — Returns customer or `404`.

**PUT** `/customers/{id}` — Same fields as POST, all optional.

**DELETE** `/customers/{id}` — Soft delete. Returns `{ "message": "Customer deleted successfully." }`
