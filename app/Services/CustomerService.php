<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function getAll(): Collection
    {
        return Customer::latest()->get();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh(); // return updated record from DB
    }

    public function delete(Customer $customer): void
    {
        $customer->delete(); // soft delete
    }
}
