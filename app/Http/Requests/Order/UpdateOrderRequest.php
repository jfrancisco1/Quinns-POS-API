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
            'customer_id'    => ['sometimes', 'integer', 'exists:customers,id'],
            'fulfillmentType' => ['sometimes', 'string', 'in:walk-in,pickup-deliver'],
            'subtotal'       => ['sometimes', 'numeric', 'min:0'],
            'deliveryFee'    => ['sometimes', 'numeric', 'min:0'],
            'total'          => ['sometimes', 'numeric', 'min:0'],
            'paymentStatus'  => ['sometimes', 'string', 'in:unpaid,pending,paid_gcash,paid_cash'],
            'orderStatus'    => ['sometimes', 'string', 'in:in_progress,ready,completed'],
            'items'          => ['sometimes', 'array', 'min:1'],
            'items.*.itemId' => ['required_with:items', 'string'],
            'items.*.label'  => ['required_with:items', 'string'],
            'items.*.unit'   => ['required_with:items', 'string'],
            'items.*.qty'    => ['required_with:items', 'integer', 'min:1'],
            'items.*.price'  => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
