<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function salesByItem(Request $request): JsonResponse
    {
        $request->validate([
            'period'    => ['required', 'in:today,this_week,this_month,this_year,custom'],
            'from'      => ['required_if:period,custom', 'date'],
            'to'        => ['required_if:period,custom', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        [$from, $to] = $this->resolveDateRange($request->period, $request->from, $request->to);

        $branchId = $request->integer('branch_id') ?: null;
        $result   = $this->reportService->salesByItem($from, $to, $branchId);

        return response()->json(array_merge(['from' => $from, 'to' => $to], $result));
    }

    public function salesByPaymentType(Request $request): JsonResponse
    {
        $request->validate([
            'period'    => ['required', 'in:today,this_week,this_month,this_year,custom'],
            'from'      => ['required_if:period,custom', 'date'],
            'to'        => ['required_if:period,custom', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        [$from, $to] = $this->resolveDateRange($request->period, $request->from, $request->to);

        $branchId = $request->integer('branch_id') ?: null;
        $result   = $this->reportService->salesByPaymentType($from, $to, $branchId);

        return response()->json(array_merge(['from' => $from, 'to' => $to], $result));
    }

    public function sales(Request $request): JsonResponse
    {
        $request->validate([
            'period'    => ['required', 'in:today,this_week,this_month,this_year,custom'],
            'from'      => ['required_if:period,custom', 'date'],
            'to'        => ['required_if:period,custom', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        [$from, $to] = $this->resolveDateRange($request->period, $request->from, $request->to);

        $branchId = $request->integer('branch_id') ?: null;
        $summary  = $this->reportService->salesSummary($from, $to, $branchId);

        return response()->json(array_merge(['from' => $from, 'to' => $to], $summary));
    }

    private function resolveDateRange(string $period, ?string $from, ?string $to): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today'      => [$now->toDateString(), $now->toDateString()],
            'this_week'  => [$now->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'this_month' => [$now->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'this_year'  => [$now->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'custom'     => [$from, $to],
        };
    }
}
