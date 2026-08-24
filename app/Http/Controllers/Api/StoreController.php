<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateStoreRequest;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;

class StoreController extends Controller
{
    public function __construct(private readonly TenantService $tenantService) {}

    public function show(): TenantResource
    {
        return new TenantResource($this->tenantService->getCurrentForUser());
    }

    public function update(UpdateStoreRequest $request): TenantResource
    {
        $tenant = $this->tenantService->updateCurrentForUser($request->validated());
        return new TenantResource($tenant);
    }
}
