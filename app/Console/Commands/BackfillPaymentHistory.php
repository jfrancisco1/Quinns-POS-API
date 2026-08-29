<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPaymentHistory extends Command
{
    protected $signature = 'payment-history:backfill {--apply : Actually write the missing rows (default is a dry run)}';

    protected $description = 'Insert an initial payment_history row for paid orders that have none, so they are counted in date_basis=paid reports';

    public function handle(): int
    {
        $affected = Order::query()
            ->where('payment_status', 'like', 'paid%')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_history')
                    ->whereColumn('payment_history.order_number', 'orders.order_number')
                    ->whereColumn('payment_history.to_status', 'orders.payment_status');
            })
            ->get(['order_number', 'payment_status', 'created_at', 'subtotal', 'delivery_fee', 'discount_amount']);

        if ($affected->isEmpty()) {
            $this->info('No affected orders found. Nothing to backfill.');

            return self::SUCCESS;
        }

        $totalAmount = $affected->sum(fn (Order $o) => $o->subtotal + $o->delivery_fee - $o->discount_amount);
        $minDate     = $affected->min('created_at');
        $maxDate     = $affected->max('created_at');

        $this->info("Affected orders: {$affected->count()}");
        $this->info("Date range: {$minDate} to {$maxDate}");
        $this->info('Total amount: ' . number_format($totalAmount, 2));
        $this->table(
            ['payment_status', 'count'],
            $affected->groupBy('payment_status')->map(fn ($g, $status) => [$status, $g->count()])->values()
        );

        if (! $this->option('apply')) {
            $this->warn('Dry run only — no rows written. Re-run with --apply to insert the missing payment_history rows.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Insert {$affected->count()} payment_history rows now?")) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($affected) {
            foreach ($affected as $order) {
                PaymentHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => 'unpaid',
                    'to_status'    => $order->payment_status,
                    'changed_at'   => $order->created_at,
                    'updated_by'   => null,
                ]);
            }
        });

        $this->info("Inserted {$affected->count()} payment_history rows.");

        return self::SUCCESS;
    }
}
