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
            'orderNumber'            => ['required', 'string', 'unique:orders,order_number'],
            'customer_nickname'      => ['required', 'string', 'max:255'],
            'customer_mobile'        => ['required', 'string', 'max:20'],
            'customer_address'       => ['nullable', 'string'],
            'customer_notes'         => ['nullable', 'string'],
            'customer_delivery_fee'  => ['nullable', 'numeric', 'min:0'],
            'fulfillmentType'        => ['required', 'string', 'in:walk-in,delivery'],
            'subtotal'               => ['required', 'numeric', 'min:0'],
            'deliveryFee'            => ['required', 'numeric', 'min:0'],
            'total'                  => ['required', 'numeric', 'min:0'],
            'createdAt'              => ['nullable', 'string'],
            'paymentStatus'          => ['nullable', 'string', 'in:paid,unpaid'],
            'orderStatus'            => ['nullable', 'string', 'in:in_progress,ready,completed,cancelled'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.itemId'         => ['required', 'string'],
            'items.*.label'          => ['required', 'string'],
            'items.*.unit'           => ['required', 'string'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
            'items.*.price'          => ['required', 'numeric', 'min:0'],
        ];
    }
}
