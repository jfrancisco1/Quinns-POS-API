<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_nickname'      => ['sometimes', 'string', 'max:255'],
            'customer_mobile'        => ['sometimes', 'string', 'max:20'],
            'customer_address'       => ['sometimes', 'nullable', 'string'],
            'customer_notes'         => ['sometimes', 'nullable', 'string'],
            'customer_delivery_fee'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'fulfillmentType'        => ['sometimes', 'string', 'in:walk-in,delivery'],
            'subtotal'               => ['sometimes', 'numeric', 'min:0'],
            'deliveryFee'            => ['sometimes', 'numeric', 'min:0'],
            'total'                  => ['sometimes', 'numeric', 'min:0'],
            'paymentStatus'          => ['sometimes', 'string', 'in:paid,unpaid'],
            'orderStatus'            => ['sometimes', 'string', 'in:in_progress,ready,completed,cancelled'],
            'items'                  => ['sometimes', 'array', 'min:1'],
            'items.*.itemId'         => ['required_with:items', 'string'],
            'items.*.label'          => ['required_with:items', 'string'],
            'items.*.unit'           => ['required_with:items', 'string'],
            'items.*.qty'            => ['required_with:items', 'integer', 'min:1'],
            'items.*.price'          => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
