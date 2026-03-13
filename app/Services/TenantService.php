<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class TenantService
{
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
        return Tenant::create($data);
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
}
