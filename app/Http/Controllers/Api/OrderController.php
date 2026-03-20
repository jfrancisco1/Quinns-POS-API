<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Item;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $from            = request()->query('from');
        $to              = request()->query('to');
        $paymentStatus   = request()->query('payment_status');
        $orderStatus     = request()->query('order_status');
        $fulfillmentType = request()->query('fulfillment_type');

        $orders = $this->orderService->getAll($from, $to, $paymentStatus, $orderStatus, $fulfillmentType);
        $this->enrichItemMeta($orders->pluck('items')->flatten());
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): OrderResource
    {
        $order = $this->orderService->create($request->validated());
        return new OrderResource($order);
    }

    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items', 'branch']);
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

    public function destroy(Order $order): JsonResponse
    {
        $this->orderService->delete($order);
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
