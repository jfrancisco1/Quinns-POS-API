<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function salesByItem(Request $request): JsonResponse
    {
        $request->validate([
            'from'      => ['required', 'date'],
            'to'        => ['required', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $branchId = $request->integer('branch_id') ?: null;
        $branch   = $this->resolveBranch($branchId);
        $result   = $this->reportService->salesByItem($request->from, $request->to, $branchId);

        return response()->json(array_merge(['from' => $request->from, 'to' => $request->to, 'branch' => $branch], $result));
    }

    public function salesByPaymentType(Request $request): JsonResponse
    {
        $request->validate([
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'branch_id'   => ['nullable', 'integer'],
            'date_basis'  => ['nullable', 'in:created,paid'],
        ]);

        $branchId  = $request->integer('branch_id') ?: null;
        $dateBasis = $request->input('date_basis', 'created');
        $branch    = $this->resolveBranch($branchId);
        $result    = $this->reportService->salesByPaymentType($request->from, $request->to, $branchId, $dateBasis);

        return response()->json(array_merge(['from' => $request->from, 'to' => $request->to, 'branch' => $branch], $result));
    }

    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'from'      => ['required', 'date'],
            'to'        => ['required', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $branchId = $request->integer('branch_id') ?: null;
        $branch   = $this->resolveBranch($branchId);
        $summary  = $this->reportService->salesSummary($request->from, $request->to, $branchId);

        return response()->json(array_merge([
            'from'   => $request->from . ' 00:00:00',
            'to'     => $request->to . ' 23:59:59',
            'branch' => $branch,
        ], $summary));
    }

    private function resolveBranch(?int $branchId): ?array
    {
        $user = Auth::user();
        $role = $user?->role ?? 'admin';

        if (in_array($role, ['staff', 'delivery'])) {
            $branch = Branch::find($user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $branch = Branch::where('id', $branchId)
                ->where('tenant_id', $user->tenant_id)
                ->first();
        } else {
            return null;
        }

        if (! $branch) {
            return null;
        }

        return [
            'id'      => $branch->id,
            'name'    => $branch->name,
            'address' => $branch->address,
            'phone'   => $branch->phone,
        ];
    }

}
