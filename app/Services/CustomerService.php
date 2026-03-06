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

    public function getAll(): Collection
    {
        return $this->tenantScope()
            ->latest()
            ->get();
    }

    public function findById(int $id): Customer
    {
        return $this->tenantScope()
            ->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        $user = Auth::user();

        return Customer::create([
            ...$data,
            'tenant_id' => $user->tenant_id ?? 1,
            'branch_id' => $user->branch_id ?? 1,
        ]);
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
