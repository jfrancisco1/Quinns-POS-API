<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class OwnerUserService
{
    public function getAllForTenant(Tenant $tenant): Collection
    {
        return $tenant->users()
            ->with('branch:id,name')
            ->where('role', '!=', 'owner')
            ->latest()
            ->get();
    }

    public function create(Tenant $tenant, array $data): User
    {
        return $tenant->users()->create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh('branch:id,name');
    }

    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }
}
