<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'orderNumber'    => $this->order_number,
            'customer_id'    => $this->customer_id,
            'customer'       => new CustomerResource($this->whenLoaded('customer')),
            'fulfillmentType' => $this->fulfillment_type,
            'subtotal'       => $this->subtotal,
            'deliveryFee'    => $this->delivery_fee,
            'discountAmount' => $this->discount_amount,
            'total'          => $this->total,
            'createdAt'      => $this->created_at_client?->toISOString() ?? $this->created_at->toISOString(),
            'paymentStatus'  => $this->payment_status,
            'orderStatus'    => $this->order_status,
            'branch'         => $this->whenLoaded('branch', fn() => ['id' => $this->branch->id, 'name' => $this->branch->name]),
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
