<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    protected abstract function model(): string;

    protected function tenantScope(): Builder
    {
        $user = Auth::user();
        $model = $this->model();

        $tenant_id = $user?->tenant_id ?? 1;
        $branch_id = $user?->branch_id ?? 1;
        $role = $user?->role ?? 'admin';

        $query = $model::where('tenant_id', $tenant_id);

        // staff and delivery are scoped to their branch only
        // admin sees all branches
        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('branch_id', $branch_id);
        }

        return $query;
    }

    protected function authorizeTenant(Model $model): void
    {
        $user = Auth::user();
        if (!$user)
            return; // skip auth check for now

        if ($model->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'staff' && $model->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized');
        }
    }
}
