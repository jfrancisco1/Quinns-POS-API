<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentHistory;
use Illuminate\Pagination\CursorPaginator;
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
        ?string $search = null,
    ): CursorPaginator {
        $query = $this->tenantScope()->with(['customer', 'items'])->orderBy('created_at', 'desc')->orderBy('order_number', 'desc');

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

        if ($search) {
            $query->whereHas('customer', fn ($q) => $q->where('nickname', 'ilike', "%{$search}%"));
        }

        return $query->cursorPaginate(30);
    }

    public function create(array $data): Order
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $clientOrderNumber = $data['orderNumber'] 
                ?? throw new \InvalidArgumentException('orderNumber is required.');

            $payload = [
                'orderNumber'       => $data['orderNumber'],
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

            $order = Order::create($payload);

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

            return $order->load(['customer', 'items']);
        });
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus): Order
    {
        $this->authorizeTenant($order);

        return DB::transaction(function () use ($order, $paymentStatus) {
            $oldPaymentStatus = $order->payment_status;

            $order->update(['payment_status' => $paymentStatus]);
            $order->refresh();

            if ($paymentStatus !== $oldPaymentStatus) {
                PaymentHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => $oldPaymentStatus,
                    'to_status'    => $paymentStatus,
                    'changed_at'   => now(),
                ]);
            }

            return $order->load(['customer', 'items']);
        });
    }

    public function updateOrderStatus(Order $order, string $orderStatus): Order
    {
        $this->authorizeTenant($order);

        return DB::transaction(function () use ($order, $orderStatus) {
            $oldOrderStatus = $order->order_status;

            $order->update(['order_status' => $orderStatus]);
            $order->refresh();

            if ($orderStatus !== $oldOrderStatus) {
                OrderStatusHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => $oldOrderStatus,
                    'to_status'    => $orderStatus,
                    'changed_at'   => now(),
                ]);
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
