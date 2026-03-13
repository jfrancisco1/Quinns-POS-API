<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class BranchService
{
    public function getAllForTenant(Tenant $tenant): Collection
    {
        return $tenant->branches()->withCount('users')->latest()->get();
    }

    public function findById(Tenant $tenant, int $branchId): Branch
    {
        return $tenant->branches()->withCount('users')->findOrFail($branchId);
    }

    public function create(Tenant $tenant, array $data): Branch
    {
        return $tenant->branches()->create($data);
    }

    public function update(Branch $branch, array $data): Branch
    {
        $branch->update($data);
        return $branch->fresh();
    }

    public function delete(Branch $branch): void
    {
        $branch->delete();
    }
}
