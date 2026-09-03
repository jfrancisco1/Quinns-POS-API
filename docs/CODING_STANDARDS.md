# Coding Standards

Standards for the Laundry POS API (Laravel 10, multi-tenant REST API).

---

## General

- PHP 8.2+ syntax. Use constructor property promotion, readonly properties, named arguments, and match expressions where they improve clarity.
- Follow PSR-12 coding style. Run `vendor/bin/pint` before committing.
- No dead code, commented-out blocks, or `dd()`/`dump()` left in.

---

## Request Flow

```
routes/api.php → Controller → Service → Model → Database
```

- **Controllers** — thin. Accept a Request, call the Service, return a Resource or JsonResponse. No business logic.
- **Services** — all business logic lives here. Extend `BaseService` for tenant-scoped resources; superadmin services do NOT extend it.
- **Models** — relationships, casts, and `resolveRouteBinding()` for tenancy enforcement. No business logic.
- **Form Requests** — validation and authorization always in a dedicated `App\Http\Requests\{Resource}\Store{Resource}Request` / `Update{Resource}Request`.
- **Resources** — transform model output via `App\Http\Resources\{Resource}Resource`.

---

## Multi-Tenancy

Every non-superadmin resource **must** carry `tenant_id` and (where applicable) `branch_id`.

- Use `tenantScope()` from `BaseService` for all list queries — never write a raw `where('tenant_id', ...)` in a controller.
- Call `authorizeTenant($model)` in service methods before mutating or returning a single record.
- Route model binding (`resolveRouteBinding()`) enforces tenancy at the model level.
- Superadmin routes (`/api/v1/superadmin/*`) are exempt from tenant scoping.

Role access matrix:

| Role         | Scope                              |
|--------------|------------------------------------|
| `superadmin` | All tenants (superadmin routes only)|
| `admin`      | All branches within their tenant   |
| `staff`      | Their branch only                  |
| `delivery`   | Their branch only                  |

---

## Controllers

```php
class FooController extends Controller
{
    public function __construct(
        private readonly FooService $fooService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return FooResource::collection($this->fooService->getAll());
    }

    public function store(StoreFooRequest $request): FooResource
    {
        return new FooResource($this->fooService->create($request->validated()));
    }

    public function show(Foo $foo): FooResource
    {
        return new FooResource($foo);
    }

    public function update(UpdateFooRequest $request, Foo $foo): FooResource
    {
        return new FooResource($this->fooService->update($foo, $request->validated()));
    }

    public function destroy(Foo $foo): JsonResponse
    {
        $this->fooService->delete($foo);
        return response()->json(['message' => 'Foo deleted successfully']);
    }
}
```

---

## Services

```php
class FooService extends BaseService
{
    protected function model(): string
    {
        return Foo::class;
    }

    public function getAll(): Collection
    {
        return $this->tenantScope()->get();
    }

    public function create(array $data): Foo
    {
        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['branch_id'] = Auth::user()->branch_id;
        return Foo::create($data);
    }

    public function update(Foo $foo, array $data): Foo
    {
        $this->authorizeTenant($foo);
        $foo->update($data);
        return $foo->fresh();
    }

    public function delete(Foo $foo): void
    {
        $this->authorizeTenant($foo);
        $foo->delete();
    }
}
```

---

## HTTP Responses

| Situation            | Status | Shape                                                 |
|----------------------|--------|-------------------------------------------------------|
| Single resource      | 200    | `FooResource` (wrapped in `data` by Laravel)          |
| Created resource     | 201    | `FooResource` (use `->response()->setStatusCode(201)`) |
| Collection           | 200    | `FooResource::collection($items)`                     |
| Successful delete    | 200    | `{"message": "Foo deleted successfully"}`             |
| Validation failure   | 422    | Laravel default (field errors under `errors`)         |
| Not found            | 404    | Laravel default                                       |
| Unauthorized         | 403    | `abort(403, 'Unauthorized')`                          |

---

## Routing

- All routes are under the `/api/v1` prefix.
- Use `apiResource` for standard CRUD. Add extra actions as explicit routes below the resource.
- Authenticated routes go inside `Route::middleware('auth:sanctum')->group(...)`.
- Superadmin routes go inside `Route::middleware(['auth:sanctum', 'superadmin'])->prefix('superadmin')->group(...)`.

```php
// Standard resource
Route::apiResource('foos', FooController::class);

// Extra action
Route::post('foos/{foo}/action', [FooController::class, 'action']);
```

---

## Form Requests

- Always use `$request->validated()` — never `$request->all()` or `$request->input()` directly in services.
- `authorize()` returns `true` unless role-based logic is needed (handled in middleware/service).

```php
class StoreFooRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
        ];
    }
}
```

---

## Models

- Use UUID primary keys (`$incrementing = false`, `$keyType = 'string'`).
- Define `$fillable` explicitly — never use `$guarded = []`.
- Soft deletes (`SoftDeletes`) for customer-facing records where appropriate.
- Cast booleans, dates, and decimals in `$casts`.

---

## Database / Migrations

- Migrations are irreversible in production — write `down()` only for local rollbacks.
- PostgreSQL enum workaround: use raw SQL to drop and recreate check constraints instead of `enum` column type changes.
- Foreign keys should reference `uuid` columns, not integer ids.

---

## Naming Conventions

| Thing              | Convention                              | Example                        |
|--------------------|-----------------------------------------|--------------------------------|
| Controller         | `{Resource}Controller`                  | `CustomerController`           |
| Service            | `{Resource}Service`                     | `CustomerService`              |
| Form Request       | `Store{Resource}Request`                | `StoreCustomerRequest`         |
| API Resource       | `{Resource}Resource`                    | `CustomerResource`             |
| Migration          | `create_{table}_table`                  | `create_customers_table`       |
| Route param        | singular snake_case                     | `{customer}`                   |
| JSON response keys | snake_case                              | `tenant_id`, `created_at`      |

---

## Documentation

**Whenever you add, change, or remove an API endpoint, you must update `resources/views/documentation.blade.php`.**

This includes:
- New endpoints → add a row to the relevant endpoint table and any new field tables.
- Changed request fields → update the fields table for that endpoint.
- Changed response shape → update the example JSON block.
- Removed endpoints → remove their entries entirely.
- New resource sections → add a `<section>` block and a sidebar `<a>` link.

The documentation page is the single source of truth for consumers of this API.
