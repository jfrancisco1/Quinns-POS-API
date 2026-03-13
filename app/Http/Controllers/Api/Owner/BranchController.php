<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Tenant;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $branchService) {}

    public function index(Tenant $tenant): AnonymousResourceCollection
    {
        return BranchResource::collection($this->branchService->getAllForTenant($tenant));
    }

    public function store(StoreBranchRequest $request, Tenant $tenant): BranchResource
    {
        $branch = $this->branchService->create($tenant, $request->validated());
        return new BranchResource($branch);
    }

    public function show(Tenant $tenant, Branch $branch): BranchResource
    {
        abort_if($branch->tenant_id !== $tenant->id, 404);
        $branch->loadCount('users');
        return new BranchResource($branch);
    }

    public function update(UpdateBranchRequest $request, Tenant $tenant, Branch $branch): BranchResource
    {
        abort_if($branch->tenant_id !== $tenant->id, 404);
        $branch = $this->branchService->update($branch, $request->validated());
        return new BranchResource($branch);
    }

    public function destroy(Tenant $tenant, Branch $branch): JsonResponse
    {
        abort_if($branch->tenant_id !== $tenant->id, 404);
        $this->branchService->delete($branch);
        return response()->json(['message' => 'Branch deleted successfully.']);
    }
}
