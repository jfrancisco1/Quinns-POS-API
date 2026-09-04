<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Resources\TenantResource;
use App\Services\RegistrationService;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService
    ) {
    }

    public function store(StoreRegistrationRequest $request): TenantResource
    {
        $tenant = $this->registrationService->register($request->validated());

        return new TenantResource($tenant);
    }
}
