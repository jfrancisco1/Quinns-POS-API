<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentHistory;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    protected function model(): string
    {
        return Order::class;
    }

    public function getAll(): Collection
    {
        return $this->tenantScope()
            ->with('items')
            ->latest()
            ->get();
    }

    public function create(array $data): Order
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'order_number'          => $data['orderNumber'],
                'customer_nickname'     => $data['customer_nickname'],
                'customer_mobile'       => $data['customer_mobile'],
                'customer_address'      => $data['customer_address'] ?? '',
                'customer_notes'        => $data['customer_notes'] ?? '',
                'customer_delivery_fee' => $data['customer_delivery_fee'] ?? 75,
                'fulfillment_type'      => $data['fulfillmentType'],
                'subtotal'              => $data['subtotal'],
                'delivery_fee'          => $data['deliveryFee'],
                'total'                 => $data['total'],
                'created_at_client'     => isset($data['createdAt']) ? \Carbon\Carbon::parse($data['createdAt']) : now(),
                'payment_status'        => $data['paymentStatus'] ?? 'unpaid',
                'order_status'          => $data['orderStatus'] ?? 'in_progress',
                'tenant_id'             => $user->tenant_id ?? 1,
                'branch_id'             => $user->branch_id ?? 1,
            ]);

            $this->syncItems($order, $data['items']);

            return $order->load('items');
        });
    }

    public function update(Order $order, array $data): Order
    {
        $this->authorizeTenant($order);

        return DB::transaction(function () use ($order, $data) {
            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus   = $order->order_status;

            $order->update(array_filter([
                'customer_nickname'     => $data['customer_nickname'] ?? null,
                'customer_mobile'       => $data['customer_mobile'] ?? null,
                'customer_address'      => $data['customer_address'] ?? null,
                'customer_notes'        => $data['customer_notes'] ?? null,
                'customer_delivery_fee' => $data['customer_delivery_fee'] ?? null,
                'fulfillment_type'      => $data['fulfillmentType'] ?? null,
                'subtotal'              => $data['subtotal'] ?? null,
                'delivery_fee'          => $data['deliveryFee'] ?? null,
                'total'                 => $data['total'] ?? null,
                'payment_status'        => $data['paymentStatus'] ?? null,
                'order_status'          => $data['orderStatus'] ?? null,
            ], fn($v) => $v !== null));

            $order->refresh();

            if (isset($data['paymentStatus']) && $data['paymentStatus'] !== $oldPaymentStatus) {
                PaymentHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => $oldPaymentStatus,
                    'to_status'    => $data['paymentStatus'],
                    'changed_at'   => now(),
                ]);
            }

            if (isset($data['orderStatus']) && $data['orderStatus'] !== $oldOrderStatus) {
                OrderStatusHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => $oldOrderStatus,
                    'to_status'    => $data['orderStatus'],
                    'changed_at'   => now(),
                ]);
            }

            if (isset($data['items'])) {
                $this->syncItems($order, $data['items']);
            }

            return $order->load('items');
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

        $rows = array_map(fn($item) => [
            'order_number' => $order->order_number,
            'item_id'      => $item['itemId'],
            'label'        => $item['label'],
            'unit'         => $item['unit'],
            'qty'          => $item['qty'],
            'price'        => $item['price'],
        ], $items);

        $order->items()->insert($rows);
    }
}
