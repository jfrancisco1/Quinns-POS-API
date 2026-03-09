<?php

namespace App\Services;

use App\Models\Order;
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
            ->with(['customer', 'items'])
            ->latest()
            ->get();
    }

    public function create(array $data): Order
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'customer_id'       => $data['customer_id'],
                'fulfillment_type'  => $data['fulfillmentType'],
                'subtotal'          => $data['subtotal'],
                'delivery_fee'      => $data['deliveryFee'],
                'total'             => $data['total'],
                'created_at_client' => isset($data['createdAt']) ? \Carbon\Carbon::parse($data['createdAt']) : now(),
                'payment_status'    => $data['paymentStatus'] ?? 'unpaid',
                'order_status'      => $data['orderStatus'] ?? 'in_progress',
                'tenant_id'         => $user->tenant_id ?? 1,
                'branch_id'         => $user->branch_id ?? 1,
            ]);

            $order->order_number = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            $this->syncItems($order, $data['items']);

            return $order->load(['customer', 'items']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        $this->authorizeTenant($order);

        $order->update(array_filter([
            'customer_id'      => $data['customer_id'] ?? null,
            'fulfillment_type' => $data['fulfillmentType'] ?? null,
            'subtotal'         => $data['subtotal'] ?? null,
            'delivery_fee'     => $data['deliveryFee'] ?? null,
            'total'            => $data['total'] ?? null,
            'payment_status'   => $data['paymentStatus'] ?? null,
            'order_status'     => $data['orderStatus'] ?? null,
        ], fn($v) => $v !== null));

        if (isset($data['items'])) {
            $this->syncItems($order, $data['items']);
        }

        return $order->load(['customer', 'items']);
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
            'order_id' => $order->id,
            'item_id'  => $item['itemId'],
            'label'    => $item['label'],
            'unit'     => $item['unit'],
            'qty'      => $item['qty'],
            'price'    => $item['price'],
        ], $items);

        $order->items()->insert($rows);
    }
}
