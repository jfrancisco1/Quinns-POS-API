<?php

namespace App\Http\Requests\PickupRequest;

use Illuminate\Foundation\Http\FormRequest;

class StorePickupRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'delivery']);
    }

    public function rules(): array
    {
        return [
            'id'               => ['required', 'uuid'],
            'branchId'         => ['required', 'integer'],
            'customerId'       => ['nullable', 'uuid'],
            'customerName'     => ['required', 'string', 'max:255'],
            'mobile'           => ['nullable', 'string', 'max:20'],
            'address'          => ['nullable', 'string', 'max:500'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'scheduledFor'     => ['nullable', 'date_format:Y-m-d'],
            'assignedDriverId' => ['nullable', 'integer'],
        ];
    }
}
