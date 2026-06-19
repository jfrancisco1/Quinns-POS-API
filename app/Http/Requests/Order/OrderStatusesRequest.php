<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderNumbers'   => ['required', 'array', 'min:1', 'max:200'],
            'orderNumbers.*' => ['required', 'string'],
        ];
    }
}
