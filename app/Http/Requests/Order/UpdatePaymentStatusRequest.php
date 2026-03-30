<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paymentStatus' => ['required', 'string', 'in:unpaid,paid_cash,paid_gcash,paid_others,paid_bank'],
        ];
    }
}
