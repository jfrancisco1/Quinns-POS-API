<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportService extends BaseService
{
    /** created_at is stored in UTC; shift to local time before grouping by hour/day/month. */
    private const LOCAL_CREATED_AT = "(created_at AT TIME ZONE 'UTC' AT TIME ZONE '" . self::LOCAL_TIMEZONE . "')";

    protected function model(): string
    {
        return Order::class;
    }

    public function salesByItem(string $from, string $to, ?int $branchId = null): array
    {
        $user     = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;
        $role     = $user?->role ?? 'admin';

        $query = Order::query()
            ->join('order_items as oi', 'oi.order_number', '=', 'orders.order_number')
            ->leftJoin('items as i', DB::raw('i.id'), '=', DB::raw('CAST(oi.item_id AS integer)'))
            ->leftJoin('categories as c', 'c.id', '=', 'i.category_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereIn('orders.payment_status', ['paid_cash', 'paid_gcash', 'paid_others', 'paid_bank'])
            ->whereBetween('orders.created_at', $this->localDayRangeUtc($from, $to));

        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('orders.branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $query->where('orders.branch_id', $branchId);
        }

        $rows = $query
            ->selectRaw('
                oi.item_id,
                oi.label,
                c.name as category,
                SUM(oi.qty) as qty,
                SUM(oi.qty * oi.price) as net_sales,
                SUM(oi.qty * COALESCE(i.cost, 0)) as cost_of_goods
            ')
            ->groupBy('oi.item_id', 'oi.label', 'c.name')
            ->orderByDesc('net_sales')
            ->get();

        $items = $rows->map(fn ($row) => [
            'item_id'       => $row->item_id,
            'label'         => $row->label,
            'category'      => $row->category,
            'qty'           => (int) $row->qty,
            'net_sales'     => (float) $row->net_sales,
            'cost_of_goods' => (float) $row->cost_of_goods,
            'gross_profit'  => (float) $row->net_sales - (float) $row->cost_of_goods,
        ])->all();

        $top5 = array_slice($items, 0, 5);

        return ['items' => $items, 'top_items' => $top5];
    }

    public function salesByPaymentType(string $from, string $to, ?int $branchId = null, string $dateBasis = 'created'): array
    {
        if ($dateBasis === 'paid') {
            return $this->salesByPaymentTypePaid($from, $to, $branchId);
        }

        $user     = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;
        $role     = $user?->role ?? 'admin';

        $query = Order::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('payment_status', ['paid_cash', 'paid_gcash', 'paid_bank', 'paid_others', 'unpaid'])
            ->whereBetween('created_at', $this->localDayRangeUtc($from, $to));

        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $rows = $query
            ->selectRaw('
                payment_status,
                COUNT(*) as transactions,
                COALESCE(SUM(subtotal), 0) as payment_amount
            ')
            ->groupBy('payment_status')
            ->orderBy('payment_status')
            ->get();

        $breakdown = $rows->map(fn ($row) => [
            'payment_method'  => $row->payment_status,
            'transactions'    => (int) $row->transactions,
            'payment_amount'  => (float) $row->payment_amount,
        ])->all();

        $unpaidRow = $rows->firstWhere('payment_status', 'unpaid');

        $unpaid = [
            'transactions' => $unpaidRow ? (int) $unpaidRow->transactions : 0,
            'amount'       => $unpaidRow ? (float) $unpaidRow->payment_amount : 0.0,
        ];

        return [
            'breakdown' => $breakdown,
            'unpaid'    => $unpaid,
        ];
    }

    /**
     * Same as salesByPaymentType, but bucketed by when each order's current
     * payment_status was last reached (payment_history.changed_at), not created_at.
     * Orders whose current status was reached more than once (e.g. a corrected
     * payment method) count once, on the date of the most recent transition into
     * that status. Currently-unpaid orders have no payment date and are excluded.
     */
    private function salesByPaymentTypePaid(string $from, string $to, ?int $branchId = null): array
    {
        $user     = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;
        $role     = $user?->role ?? 'admin';

        [$startUtc, $endUtc] = $this->localDayRangeUtc($from, $to);

        $latestPayment = DB::table('payment_history as ph')
            ->join('orders as o', 'o.order_number', '=', 'ph.order_number')
            ->whereColumn('ph.to_status', 'o.payment_status')
            ->groupBy('ph.order_number')
            ->selectRaw('ph.order_number, MAX(ph.changed_at) as changed_at');

        $query = Order::query()
            ->joinSub($latestPayment, 'latest_payment', function ($join) {
                $join->on('latest_payment.order_number', '=', 'orders.order_number');
            })
            ->where('orders.tenant_id', $tenantId)
            ->whereIn('orders.payment_status', ['paid_cash', 'paid_gcash', 'paid_bank', 'paid_others'])
            ->whereBetween('latest_payment.changed_at', [$startUtc, $endUtc]);

        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('orders.branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $query->where('orders.branch_id', $branchId);
        }

        $rows = $query
            ->selectRaw('
                orders.payment_status,
                COUNT(*) as transactions,
                COALESCE(SUM(orders.subtotal - orders.discount_amount), 0) as payment_amount
            ')
            ->groupBy('orders.payment_status')
            ->orderBy('orders.payment_status')
            ->get();

        $breakdown = $rows->map(fn ($row) => [
            'payment_method' => $row->payment_status,
            'transactions'   => (int) $row->transactions,
            'payment_amount' => (float) $row->payment_amount,
        ])->all();

        return ['breakdown' => $breakdown];
    }

    public function salesSummary(string $from, string $to, ?int $branchId = null): array
    {
        $user      = Auth::user();
        $tenantId  = $user?->tenant_id ?? 1;
        $role      = $user?->role ?? 'admin';

        $fromDate  = new \DateTimeImmutable($from);
        $toDate    = new \DateTimeImmutable($to);
        $diffDays  = (int) $fromDate->diff($toDate)->days;
        $dateRange = $this->localDayRangeUtc($from, $to);

        $baseQuery = function () use ($tenantId, $role, $user, $branchId, $dateRange) {
            $q = Order::query()
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', $dateRange);

            if (in_array($role, ['staff', 'delivery'])) {
                $q->where('branch_id', $user->branch_id);
            } elseif ($role === 'admin' && $branchId !== null) {
                $q->where('branch_id', $branchId);
            }

            return $q;
        };

        // Gross Sales and Discounts — all orders (paid + unpaid)
        $allRow = $baseQuery()->selectRaw('
            COALESCE(SUM(subtotal), 0) as gross_sales,
            COALESCE(SUM(discount_amount), 0) as total_discounts
        ')->first();

        $grossSales     = (float) $allRow->gross_sales;
        $totalDiscounts = (float) $allRow->total_discounts;
        $netSales       = $grossSales - $totalDiscounts;

        // COGS — all orders
        $cogQuery = Order::query()
            ->join('order_items as oi', 'oi.order_number', '=', 'orders.order_number')
            ->leftJoin('items as i', DB::raw('i.id'), '=', DB::raw('CAST(oi.item_id AS integer)'))
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', $dateRange);

        if (in_array($role, ['staff', 'delivery'])) {
            $cogQuery->where('orders.branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $cogQuery->where('orders.branch_id', $branchId);
        }

        $costOfGoods = (float) $cogQuery->sum(DB::raw('oi.qty * COALESCE(i.cost, 0)'));

        // Expenses
        $expenseQuery = Expense::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$from, $to]);

        if (in_array($role, ['staff', 'delivery'])) {
            $expenseQuery->where('branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $expenseQuery->where('branch_id', $branchId);
        }

        $totalExpenses = (float) $expenseQuery->sum('amount');

        // Collected (paid) — net of discounts
        $collected = (float) $baseQuery()
            ->whereIn('payment_status', ['paid_cash', 'paid_gcash', 'paid_others', 'paid_bank'])
            ->sum(DB::raw('subtotal - discount_amount'));

        // Outstanding (unpaid) — net of discounts
        $unpaidRow = $baseQuery()
            ->where('payment_status', 'unpaid')
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(subtotal - discount_amount), 0) as outstanding')
            ->first();

        $grossProfit = $netSales - $costOfGoods;
        $netProfit   = $grossProfit - $totalExpenses;
        $series      = $this->buildSeries($from, $to, $diffDays, $tenantId, $role, $user, $branchId);
        $groupBy     = $diffDays === 0 ? 'hour' : ($diffDays <= 31 ? 'day' : 'month');

        return [
            'grossSales'   => $grossSales,
            'discounts'    => $totalDiscounts,
            'netSales'     => $netSales,
            'costOfGoods'  => $costOfGoods,
            'grossProfit'  => $grossProfit,
            'expenses'     => $totalExpenses,
            'netProfit'    => $netProfit,
            'collected'    => $collected,
            'outstanding'  => (float) $unpaidRow->outstanding,
            'unpaidOrders' => (int) $unpaidRow->orders,
            'group_by'     => $groupBy,
            'series'       => $series,
        ];
    }

    private function buildSeries(string $from, string $to, int $diffDays, int $tenantId, string $role, $user, ?int $branchId): array
    {
        $seriesQuery = Order::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', $this->localDayRangeUtc($from, $to));

        if (in_array($role, ['staff', 'delivery'])) {
            $seriesQuery->where('branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $seriesQuery->where('branch_id', $branchId);
        }

        if ($diffDays === 0) {
            // Single day → group by hour
            $rows = $seriesQuery
                ->selectRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'HH24') as label, COALESCE(SUM(subtotal - discount_amount), 0) as net_sales")
                ->groupByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'HH24')")
                ->orderByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'HH24')")
                ->get();

            $map = $rows->keyBy('label');

            return collect(range(0, 23))->map(function ($h) use ($map) {
                $key = str_pad($h, 2, '0', STR_PAD_LEFT);
                return [
                    'label'     => $key . ':00',
                    'net_sales' => (float) ($map->get($key)?->net_sales ?? 0),
                ];
            })->all();
        }

        if ($diffDays <= 31) {
            // Week or month range → group by date
            $rows = $seriesQuery
                ->selectRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM-DD') as label, COALESCE(SUM(subtotal - discount_amount), 0) as net_sales")
                ->groupByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM-DD')")
                ->orderByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM-DD')")
                ->get();

            $map    = $rows->keyBy('label');
            $start  = new \DateTimeImmutable($from);
            $end    = new \DateTimeImmutable($to);
            $series = [];
            $cursor = $start;

            while ($cursor <= $end) {
                $key      = $cursor->format('Y-m-d');
                $series[] = [
                    'label'     => $key,
                    'net_sales' => (float) ($map->get($key)?->net_sales ?? 0),
                ];
                $cursor = $cursor->modify('+1 day');
            }

            return $series;
        }

        // Year+ range → group by month
        $rows = $seriesQuery
            ->selectRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM') as label, COALESCE(SUM(subtotal - discount_amount), 0) as net_sales")
            ->groupByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM')")
            ->orderByRaw("TO_CHAR(" . self::LOCAL_CREATED_AT . ", 'YYYY-MM')")
            ->get();

        $map    = $rows->keyBy('label');
        $start  = new \DateTimeImmutable($from);
        $end    = new \DateTimeImmutable($to);
        $series = [];
        $cursor = new \DateTimeImmutable($start->format('Y-m-01'));

        while ($cursor <= $end) {
            $key      = $cursor->format('Y-m');
            $series[] = [
                'label'     => $key,
                'net_sales' => (float) ($map->get($key)?->net_sales ?? 0),
            ];
            $cursor = $cursor->modify('+1 month');
        }

        return $series;
    }
}
