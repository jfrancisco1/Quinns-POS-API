<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant')->id;

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'slug'      => ['sometimes', 'string', 'max:255', Rule::unique('tenants', 'slug')->ignore($tenantId), 'regex:/^[a-z0-9-]+$/'],
            'email'     => ['sometimes', 'email', Rule::unique('tenants', 'email')->ignore($tenantId)],
            'phone'     => ['nullable', 'string', 'max:20', Rule::unique('tenants', 'phone')->ignore($tenantId)],
            'address'   => ['nullable', 'string', 'max:500'],
            'gcash_number' => ['nullable', 'string', 'max:20'],
            'plan'      => ['sometimes', 'in:free,basic,pro'],
            'is_active' => ['sometimes', 'boolean'],
            'trial_ends_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
