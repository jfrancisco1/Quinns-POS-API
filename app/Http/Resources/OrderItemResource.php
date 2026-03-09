<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'orderNumber' => $this->order_number,
            'itemId'      => $this->item_id,
            'label'       => $this->label,
            'unit'        => $this->unit,
            'qty'         => $this->qty,
            'price'       => $this->price,
        ];
    }
}
