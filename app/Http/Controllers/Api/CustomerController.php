<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $customers = $this->customerService->getAll();
        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        $customer = $this->customerService->create($request->validated());
        return new CustomerResource($customer);
    }

    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer = $this->customerService->update($customer, $request->validated());
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
