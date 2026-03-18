<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportService extends BaseService
{
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
            ->join('order_items as oi', 'oi.order_id', '=', 'orders.id')
            ->leftJoin('items as i', DB::raw('i.id'), '=', DB::raw('CAST(oi.item_id AS integer)'))
            ->leftJoin('categories as c', 'c.id', '=', 'i.category_id')
            ->where('orders.tenant_id', $tenantId)
            ->whereIn('orders.payment_status', ['paid_cash', 'paid_gcash'])
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

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

    public function salesByPaymentType(string $from, string $to, ?int $branchId = null): array
    {
        $user     = Auth::user();
        $tenantId = $user?->tenant_id ?? 1;
        $role     = $user?->role ?? 'admin';

        $query = Order::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('payment_status', ['paid_cash', 'paid_gcash'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $rows = $query
            ->selectRaw('
                payment_method,
                COUNT(*) as transactions,
                COALESCE(SUM(subtotal), 0) as payment_amount,
                COALESCE(SUM(subtotal - COALESCE(discount_amount, 0)), 0) as net_amount
            ')
            ->groupBy('payment_method')
            ->orderBy('payment_method')
            ->get();

        $breakdown = $rows->map(fn ($row) => [
            'payment_method'  => $row->payment_method,
            'transactions'    => (int) $row->transactions,
            'payment_amount'  => (float) $row->payment_amount,
            'net_amount'      => (float) $row->net_amount,
        ])->all();

        return ['breakdown' => $breakdown];
    }

    public function salesSummary(string $from, string $to, ?int $branchId = null): array
    {
        $user      = Auth::user();
        $tenantId  = $user?->tenant_id ?? 1;
        $role      = $user?->role ?? 'admin';

        $query = Order::query()
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'orders.id')
            ->leftJoin('items as i', DB::raw('i.id'), '=', DB::raw('CAST(oi.item_id AS integer)'))
            ->where('orders.tenant_id', $tenantId)
            ->whereIn('orders.payment_status', ['paid_cash', 'paid_gcash'])
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if (in_array($role, ['staff', 'delivery'])) {
            $query->where('orders.branch_id', $user->branch_id);
        } elseif ($role === 'admin' && $branchId !== null) {
            $query->where('orders.branch_id', $branchId);
        }

        $row = $query->selectRaw('
                COALESCE(SUM(orders.subtotal), 0) as gross_sales,
                COALESCE(SUM(orders.discount_amount), 0) as discounts,
                COALESCE(SUM(oi.qty * COALESCE(i.cost, 0)), 0) as cost_of_goods
            ')
            ->first();

        $grossSales  = (float) $row->gross_sales;
        $discounts   = (float) $row->discounts;
        $netSales    = $grossSales - $discounts;
        $costOfGoods = (float) $row->cost_of_goods;

        return [
            'grossSales'  => $grossSales,
            'discounts'   => $discounts,
            'netSales'    => $netSales,
            'costOfGoods' => $costOfGoods,
            'grossProfit' => $netSales - $costOfGoods,
        ];
    }
}
