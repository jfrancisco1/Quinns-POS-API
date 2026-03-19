<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'    => ['required', 'string', 'uuid', 'exists:customers,id'],
            'fulfillmentType' => ['required', 'string', 'in:walk-in,pickup-deliver'],
            'subtotal'       => ['required', 'numeric', 'min:0'],
            'deliveryFee'    => ['required', 'numeric', 'min:0'],
            'total'          => ['required', 'numeric', 'min:0'],
            'createdAt'      => ['nullable', 'string'],
            'paymentStatus'  => ['nullable', 'string', 'in:unpaid,paid_gcash,paid_cash,paid_others'],
            'discountAmount' => ['nullable', 'numeric', 'min:0'],
            'orderStatus'    => ['nullable', 'string', 'in:in_progress,ready,completed'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.itemId' => ['required', 'string'],
            'items.*.label'  => ['required', 'string'],
            'items.*.qty'    => ['required', 'integer', 'min:1'],
            'items.*.price'  => ['required', 'numeric', 'min:0'],
        ];
    }
}
