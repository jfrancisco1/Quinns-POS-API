<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OwnerUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly OwnerUserService $userService) {}

    public function index(Tenant $tenant): AnonymousResourceCollection
    {
        return UserResource::collection($this->userService->getAllForTenant($tenant));
    }

    public function store(StoreUserRequest $request, Tenant $tenant): UserResource
    {
        $user = $this->userService->create($tenant, $request->validated());
        return new UserResource($user->load('branch'));
    }

    public function show(Tenant $tenant, User $user): UserResource
    {
        abort_if($user->tenant_id !== $tenant->id, 404);
        return new UserResource($user->load('branch'));
    }

    public function update(UpdateUserRequest $request, Tenant $tenant, User $user): UserResource
    {
        abort_if($user->tenant_id !== $tenant->id, 404);
        $user = $this->userService->update($user, $request->validated());
        return new UserResource($user);
    }

    public function toggle(Tenant $tenant, User $user): UserResource
    {
        abort_if($user->tenant_id !== $tenant->id, 404);
        return new UserResource($this->userService->toggleActive($user));
    }

    public function destroy(Tenant $tenant, User $user): JsonResponse
    {
        abort_if($user->tenant_id !== $tenant->id, 404);
        $this->userService->delete($user);
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
