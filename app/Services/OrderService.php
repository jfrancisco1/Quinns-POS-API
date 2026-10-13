<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    protected function model(): string
    {
        return Order::class;
    }

    /** Drivers serve every branch of their tenant, so delivery is not branch-scoped for orders. */
    protected function tenantScope(): Builder
    {
        if (Auth::user()?->role === 'delivery') {
            return Order::where('tenant_id', Auth::user()->tenant_id);
        }

        return parent::tenantScope();
    }

    public function getAll(
        ?string $from = null,
        ?string $to = null,
        ?string $paymentStatus = null,
        ?string $orderStatus = null,
        ?string $fulfillmentType = null,
        ?string $search = null,
        ?int $branchId = null,
        string $dateBasis = 'created',
        ?string $assignedTo = null,
    ): CursorPaginator {
        $query = $this->tenantScope()->with(['customer', 'items', 'branch', 'assignedDriver', 'paymentHistory.updatedBy', 'orderStatusHistory.updatedBy'])->orderBy('orders.created_at', 'desc')->orderBy('orders.order_number', 'desc');

        if ($branchId && in_array(Auth::user()?->role, ['admin', 'delivery'])) {
            $query->where('branch_id', $branchId);
        }

        if ($assignedTo === 'me') {
            $query->where('assigned_driver_id', Auth::id());
        } elseif ($assignedTo === 'none') {
            $query->whereNull('assigned_driver_id');
        }

        if ($from && $to) {
            if ($dateBasis === 'paid') {
                [$startUtc, $endUtc] = $this->localDayRangeUtc($from, $to);

                // Looked up per order so a page stops after 30 matches, instead of aggregating
                // every tenant's payment history first. A payment is never recorded before its
                // order was created, so orders created after the range can be skipped.
                $query
                    ->whereIn('orders.payment_status', ['paid_cash', 'paid_gcash', 'paid_bank', 'paid_others'])
                    ->where('orders.created_at', '<=', $endUtc)
                    ->whereRaw('
                        (SELECT MAX(ph.changed_at) FROM payment_history ph
                         WHERE ph.order_number = orders.order_number AND ph.to_status = orders.payment_status)
                        BETWEEN ? AND ?
                    ', [$startUtc, $endUtc]);
            } else {
                $query->whereBetween('created_at', $this->localDayRangeUtc($from, $to));
            }
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
                'order_number'      => $clientOrderNumber,
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

            $order = Order::updateOrCreate(['order_number' => $clientOrderNumber], $payload);

            if ($order->wasRecentlyCreated && $order->payment_status !== 'unpaid') {
                PaymentHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => 'unpaid',
                    'to_status'    => $order->payment_status,
                    'changed_at'   => $order->created_at,
                    'updated_by'   => Auth::id(),
                ]);
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
                    'order_number' => $order->order_number,
                    'from_status'  => $oldPaymentStatus,
                    'to_status'    => $data['paymentStatus'],
                    'changed_at'   => now(),
                    'updated_by'   => Auth::id(),
                ]);
            }

            if (isset($data['orderStatus']) && $data['orderStatus'] !== $oldOrderStatus) {
                OrderStatusHistory::create([
                    'order_number' => $order->order_number,
                    'from_status'  => $oldOrderStatus,
                    'to_status'    => $data['orderStatus'],
                    'changed_at'   => now(),
                    'updated_by'   => Auth::id(),
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
                    'updated_by'   => Auth::id(),
                ]);
            }

            return $order->load(['customer', 'items', 'paymentHistory.updatedBy']);
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
                    'updated_by'   => Auth::id(),
                ]);
            }

            return $order->load(['customer', 'items', 'orderStatusHistory.updatedBy']);
        });
    }

    /**
     * Claims (driver = themselves), assigns (admin, any driver) or releases (`null`) an order
     * for delivery. Repeating the current state is a no-op, so offline retries are safe.
     * A driver touching an order another driver holds gets 409.
     */
    public function assign(Order $order, ?int $driverId): Order
    {
        $this->authorizeTenant($order);
        $user = Auth::user();

        if ($user->role === 'delivery' && $driverId !== null && $driverId !== $user->id) {
            abort(403, 'Drivers can only claim orders for themselves.');
        }

        return DB::transaction(function () use ($order, $driverId, $user) {
            // Locked so two drivers claiming at the same moment can't both win.
            $order = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $current = $order->assigned_driver_id;

            if ($current === $driverId) {
                return $this->loadForDriver($order);
            }

            if ($user->role === 'delivery' && $current !== null && $current !== $user->id) {
                $this->conflict($order, 'This order is already being delivered by another driver.');
            }

            if ($driverId === null) {
                $order->update(['assigned_driver_id' => null, 'delivery_status' => 'unassigned', 'assigned_at' => null]);
                return $this->loadForDriver($order);
            }

            if (in_array($order->order_status, ['completed', 'cancelled'])) {
                abort(422, 'This order is already closed.');
            }

            $driverExists = User::where('id', $driverId)
                ->where('tenant_id', $order->tenant_id)
                ->where('role', 'delivery')
                ->exists();
            abort_unless($driverExists, 422, 'Driver not found.');

            $order->update([
                'assigned_driver_id'     => $driverId,
                'delivery_status'        => 'assigned',
                'assigned_at'            => now(),
                'delivery_failed_reason' => null,
            ]);

            return $this->loadForDriver($order);
        });
    }

    /**
     * `delivered` completes the order (claiming it first if it was still in the pool).
     * `failed` puts it back in the pool with the reason.
     */
    public function updateDeliveryStatus(Order $order, string $deliveryStatus, ?string $reason = null): Order
    {
        $this->authorizeTenant($order);
        $user = Auth::user();

        $order = DB::transaction(function () use ($order, $deliveryStatus, $reason, $user) {
            $order = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $holder = $order->assigned_driver_id;

            if ($deliveryStatus === 'delivered' && $order->delivery_status === 'delivered') {
                return $order;
            }

            if ($user->role === 'delivery' && $holder !== null && $holder !== $user->id) {
                $this->conflict($order, 'This order is assigned to another driver.');
            }

            if ($deliveryStatus === 'delivered') {
                $order->update([
                    'assigned_driver_id' => $holder ?? ($user->role === 'delivery' ? $user->id : null),
                    'assigned_at'        => $order->assigned_at ?? now(),
                    'delivery_status'    => 'delivered',
                ]);
            } else {
                $order->update([
                    'assigned_driver_id'     => null,
                    'assigned_at'            => null,
                    'delivery_status'        => 'unassigned',
                    'delivery_failed_reason' => $reason,
                ]);
            }

            return $order;
        });

        if ($deliveryStatus === 'delivered' && $order->order_status !== 'completed') {
            $this->updateOrderStatus($order, 'completed');
        }

        return $this->loadForDriver($order);
    }

    private function loadForDriver(Order $order): Order
    {
        return $order->refresh()->load(['customer', 'items', 'branch', 'assignedDriver', 'paymentHistory.updatedBy', 'orderStatusHistory.updatedBy']);
    }

    /** 409 with who holds the order, so the app can say "Already taken by Juan". */
    private function conflict(Order $order, string $message): never
    {
        $holder = $order->assignedDriver()->first(['id', 'name']);

        throw new HttpResponseException(response()->json([
            'message'        => $message,
            'assignedDriver' => $holder ? ['id' => $holder->id, 'name' => $holder->name] : null,
        ], 409));
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
