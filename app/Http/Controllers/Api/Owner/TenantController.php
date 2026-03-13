<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\BranchResource;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function __construct(private readonly TenantService $tenantService) {}

    public function index(): AnonymousResourceCollection
    {
        return TenantResource::collection($this->tenantService->getAll());
    }

    public function store(StoreTenantRequest $request): TenantResource
    {
        $tenant = $this->tenantService->create($request->validated());
        return new TenantResource($tenant);
    }

    public function show(Tenant $tenant): TenantResource
    {
        $tenant->loadCount(['branches', 'users']);
        $tenant->load('branches');
        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): TenantResource
    {
        $tenant = $this->tenantService->update($tenant, $request->validated());
        return new TenantResource($tenant);
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->tenantService->delete($tenant);
        return response()->json(['message' => 'Tenant deleted successfully.']);
    }
}
