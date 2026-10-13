<?php

namespace App\Http\Requests\PickupRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePickupRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'delivery']);
    }

    public function rules(): array
    {
        return [
            'action'       => ['nullable', 'string', 'in:claim,release,pick_up,reopen,receive,cancel'],
            // Admin only: assign to a driver, or null to put back in the pool.
            'driverId'     => ['nullable', 'integer'],
            'customerName' => ['sometimes', 'string', 'max:255'],
            'mobile'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'notes'        => ['sometimes', 'nullable', 'string', 'max:1000'],
            'bagCount'     => ['sometimes', 'nullable', 'integer', 'min:0', 'max:999'],
            'scheduledFor' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }
}
