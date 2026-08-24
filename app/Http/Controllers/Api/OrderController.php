<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStatusesRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdatePaymentStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Item;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    public function index(): JsonResponse
    {
        $from            = request()->query('from');
        $to              = request()->query('to');
        $paymentStatus   = request()->query('payment_status');
        $orderStatus     = request()->query('order_status');
        $fulfillmentType = request()->query('fulfillment_type');
        $search          = request()->query('search') ?: null;
        $branchId        = request()->integer('branch_id') ?: null;
        $dateBasis       = request()->query('date_basis') === 'paid' ? 'paid' : 'created';

        $paginator = $this->orderService->getAll($from, $to, $paymentStatus, $orderStatus, $fulfillmentType, $search, $branchId, $dateBasis);
        $this->enrichItemMeta($paginator->getCollection()->pluck('items')->flatten());

        return response()->json([
            'data'       => OrderResource::collection($paginator->getCollection()),
            'nextCursor' => $paginator->nextCursor()?->encode(),
            'prevCursor' => $paginator->previousCursor()?->encode(),
            'hasMore'    => $paginator->hasMorePages(),
            'perPage'    => $paginator->perPage(),
        ]);
    }

    public function store(StoreOrderRequest $request): OrderResource
    {
        $order = $this->orderService->create($request->validated());
        return new OrderResource($order);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items', 'branch', 'paymentHistory.updatedBy', 'orderStatusHistory.updatedBy']);
        $this->enrichItemMeta($order->items);
        return new OrderResource($order);
    }

    private function enrichItemMeta($orderItems): void
    {
        $itemMeta = Item::whereIn('id', $orderItems->pluck('item_id')->filter()->values())
            ->get(['id', 'color', 'shape'])
            ->keyBy(fn($i) => (string) $i->id);

        $orderItems->each(function ($orderItem) use ($itemMeta) {
            $item = $itemMeta->get((string) $orderItem->item_id);
            $orderItem->item_color = $item?->color;
            $orderItem->item_shape = $item?->shape;
        });
    }

    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        $order = $this->orderService->update($order, $request->validated());
        return new OrderResource($order);
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $order = $this->orderService->updateOrderStatus($order, $request->validated()['orderStatus']);
        return new OrderResource($order);
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, Order $order): OrderResource
    {
        $order = $this->orderService->updatePaymentStatus($order, $request->validated()['paymentStatus']);
        return new OrderResource($order);
    }

    public function statuses(OrderStatusesRequest $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $orders = Order::whereIn('order_number', $request->validated()['orderNumbers'])
            ->where('tenant_id', $tenantId)
            ->get(['order_number', 'payment_status', 'order_status']);

        return response()->json(
            $orders->map(fn($o) => [
                'orderNumber'   => $o->order_number,
                'paymentStatus' => $o->payment_status,
                'orderStatus'   => $o->order_status,
            ])->values()
        );
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->orderService->delete($order);
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
