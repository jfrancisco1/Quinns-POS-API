<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TenantService
{
    public function __construct(
        private readonly ExpenseCategoryService $expenseCategoryService
    ) {
    }

    public function getAll(): Collection
    {
        return Tenant::withCount(['branches', 'users'])->latest()->get();
    }

    public function findById(int $id): Tenant
    {
        return Tenant::withCount(['branches', 'users'])->findOrFail($id);
    }

    public function create(array $data): Tenant
    {
        if (($data['plan'] ?? null) === 'free') {
            $data['trial_ends_at'] = now()->addDays(30);
        }

        $tenant = Tenant::create($data);
        $this->expenseCategoryService->seedDefaults($tenant->id);

        return $tenant->fresh();
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant->fresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    public function getCurrentForUser(): Tenant
    {
        return Tenant::findOrFail(Auth::user()->tenant_id);
    }

    public function updateCurrentForUser(array $data): Tenant
    {
        $tenant = Tenant::findOrFail(Auth::user()->tenant_id);
        $tenant->update($data);

        return $tenant->fresh();
    }
}
