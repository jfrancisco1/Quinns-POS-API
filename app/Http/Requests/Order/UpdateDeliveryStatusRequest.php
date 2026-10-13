<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'delivery']);
    }

    public function rules(): array
    {
        return [
            'deliveryStatus' => ['required', 'string', 'in:delivered,failed'],
            'reason'         => ['nullable', 'string', 'max:255'],
        ];
    }
}
