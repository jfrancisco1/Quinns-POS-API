<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'orderNumber'          => $this->order_number,
            'customer_nickname'    => $this->customer_nickname,
            'customer_mobile'      => $this->customer_mobile,
            'customer_address'     => $this->customer_address,
            'customer_notes'       => $this->customer_notes,
            'customer_delivery_fee' => $this->customer_delivery_fee,
            'fulfillmentType'      => $this->fulfillment_type,
            'subtotal'             => $this->subtotal,
            'deliveryFee'          => $this->delivery_fee,
            'total'                => $this->total,
            'createdAt'            => $this->created_at_client?->toISOString() ?? $this->created_at->toISOString(),
            'paymentStatus'        => $this->payment_status,
            'orderStatus'          => $this->order_status,
            'items'                => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
