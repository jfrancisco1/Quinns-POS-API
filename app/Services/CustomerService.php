<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CustomerService extends BaseService
{
    protected function model(): string
    {
        return Customer::class;
    }

    public function getAll(?string $search = null): Collection
    {
        return $this->tenantScope()
            ->withMax('orders', 'created_at')
            ->withSum('orders', 'total')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('nickname', 'ilike', "%{$search}%")
                  ->orWhere('mobile', 'ilike', "%{$search}%");
            }))
            ->latest()
            ->get();
    }

    public function findById(string $id): Customer
    {
        return $this->tenantScope()
            ->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        $user = Auth::user();

        $payload = [
            ...$data,
            'tenant_id' => $user->tenant_id ?? 1,
            'branch_id' => $user->branch_id ?? $data['branch_id'] ?? 1,
        ];

        // Idempotent: client-generated UUID prevents duplicates on re-sync
        if (!empty($payload['id'])) {
            return Customer::updateOrCreate(['id' => $payload['id']], $payload);
        }

        return Customer::create($payload);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $this->authorizeTenant($customer);

        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $this->authorizeTenant($customer);

        $customer->delete();
    }
}
