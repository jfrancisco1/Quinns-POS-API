<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AssignOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'delivery']);
    }

    public function rules(): array
    {
        return [
            // Drivers: claim / release. Admin: driverId to assign, or release.
            'action'   => ['required_without:driverId', 'nullable', 'string', 'in:claim,release'],
            'driverId' => ['nullable', 'integer'],
        ];
    }
}
