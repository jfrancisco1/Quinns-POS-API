<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    protected function model(): string
    {
        return Order::class;
    }

    public function getAll(
        ?string $from = null,
        ?string $to = null,
        ?string $paymentStatus = null,
        ?string $orderStatus = null,
        ?string $fulfillmentType = null,
    ): Collection {
        $query = $this->tenantScope()->with(['customer', 'items'])->latest();

        if ($from && $to) {
            $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($orderStatus) {
            $query->where('order_status', $orderStatus);
        }

        if ($fulfillmentType) {
            $query->where('fulfillment_type', $fulfillmentType);
        }

        return $query->get();
    }

    public function create(array $data): Order
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $payload = [
                'customer_id'       => $data['customer_id'] ?? null,
                'fulfillment_type'  => $data['fulfillmentType'],
                'subtotal'          => $data['subtotal'],
                'delivery_fee'      => $data['deliveryFee'],
                'total'             => $data['total'],
                'created_at_client' => isset($data['createdAt']) ? \Carbon\Carbon::parse($data['createdAt']) : now(),
                'payment_status'    => $data['paymentStatus'] ?? 'unpaid',
                'order_status'      => $data['orderStatus'] ?? 'in_progress',
                'tenant_id'         => $user->tenant_id ?? 1,
                'branch_id'         => $user->branch_id ?? $data['branch_id'] ?? 1,
            ];

            // Idempotent: client-generated UUID prevents duplicates on re-sync
            if (!empty($data['id'])) {
                $order = Order::updateOrCreate(['id' => $data['id']], $payload);
            } else {
                $order = Order::create($payload);
            }

            if (empty($order->order_number)) {
                $count = Order::where('branch_id', $order->branch_id)->count();
                $order->order_number = 'ORD-' . str_pad($count, 5, '0', STR_PAD_LEFT);
                $order->save();
            }

            $this->syncItems($order, $data['items'] ?? []);

            return $order->load(['customer', 'items']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        $this->authorizeTenant($order);

        return DB::transaction(function () use ($order, $data) {
            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus   = $order->order_status;

            $order->update(array_filter([
                'customer_id'      => $data['customer_id'] ?? null,
                'fulfillment_type' => $data['fulfillmentType'] ?? null,
                'subtotal'         => $data['subtotal'] ?? null,
                'delivery_fee'     => $data['deliveryFee'] ?? null,
                'total'            => $data['total'] ?? null,
                'payment_status'   => $data['paymentStatus'] ?? null,
                'order_status'     => $data['orderStatus'] ?? null,
            ], fn($v) => $v !== null));

            $order->refresh();

            if (isset($data['paymentStatus']) && $data['paymentStatus'] !== $oldPaymentStatus) {
                PaymentHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => $oldPaymentStatus,
                    'to_status'   => $data['paymentStatus'],
                    'changed_at'  => now(),
                ]);
            }

            if (isset($data['orderStatus']) && $data['orderStatus'] !== $oldOrderStatus) {
                OrderStatusHistory::create([
                    'order_id'    => $order->id,
                    'from_status' => $oldOrderStatus,
                    'to_status'   => $data['orderStatus'],
                    'changed_at'  => now(),
                ]);
            }

            if (isset($data['items'])) {
                $this->syncItems($order, $data['items']);
            }

            return $order->load(['customer', 'items']);
        });
    }

    public function delete(Order $order): void
    {
        $this->authorizeTenant($order);
        $order->delete();
    }

    private function syncItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $item) {
            $order->items()->create([
                'item_id' => $item['itemId'],
                'label'   => $item['label'],
                'qty'     => $item['qty'],
                'price'   => $item['price'],
            ]);
        }
    }
}
