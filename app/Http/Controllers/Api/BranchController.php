<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\CopyBranchItemsRequest;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $branchService) {}

    public function index(): AnonymousResourceCollection
    {
        $user = Auth::user();

        $branches = Branch::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->get();

        return BranchResource::collection($branches);
    }

    public function store(StoreBranchRequest $request): BranchResource
    {
        $user = Auth::user();
        abort_if($user->role !== 'admin', 403, 'Only admins can create branches.');

        $branch = $this->branchService->create($user->tenant, $request->validated());

        return new BranchResource($branch);
    }

    public function show(Branch $branch): BranchResource
    {
        $user = Auth::user();
        abort_if($branch->tenant_id !== $user->tenant_id, 403, 'Unauthorized.');

        $branch->loadCount('users');

        return new BranchResource($branch);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): BranchResource
    {
        $user = Auth::user();
        abort_if($user->role !== 'admin', 403, 'Only admins can update branches.');
        abort_if($branch->tenant_id !== $user->tenant_id, 403, 'Unauthorized.');

        $branch = $this->branchService->update($branch, $request->validated());

        return new BranchResource($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $user = Auth::user();
        abort_if($user->role !== 'admin', 403, 'Only admins can delete branches.');
        abort_if($branch->tenant_id !== $user->tenant_id, 403, 'Unauthorized.');

        $this->branchService->delete($branch);

        return response()->json(['message' => 'Branch deleted successfully.']);
    }

    public function copyItems(CopyBranchItemsRequest $request, Branch $branch): JsonResponse
    {
        $user = Auth::user();
        abort_if($user->role !== 'admin', 403, 'Only admins can copy branch items.');
        abort_if($branch->tenant_id !== $user->tenant_id, 403, 'Unauthorized.');

        $source = Branch::findOrFail($request->validated('source_branch_id'));
        $result = $this->branchService->copyItems($source, $branch);

        return response()->json([
            'message' => "Copied {$result['copied']} item(s) from {$source->name} to {$branch->name}.",
            'data' => [
                'source_branch_id' => $source->id,
                'target_branch_id' => $branch->id,
                'copied' => $result['copied'],
                'skipped' => $result['skipped'],
            ],
        ]);
    }
}
