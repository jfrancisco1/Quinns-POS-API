<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = Auth::user()->tenant_id;

        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', Rule::unique('tenants', 'email')->ignore($tenantId)],
            'phone'        => ['nullable', 'string', 'max:20', Rule::unique('tenants', 'phone')->ignore($tenantId)],
            'address'      => ['nullable', 'string', 'max:500'],
            'gcash_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
