<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['required', 'string', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9-]+$/'],
            'email'   => ['required', 'email', 'unique:tenants,email'],
            'phone'   => ['nullable', 'string', 'max:20', 'unique:tenants,phone'],
            'address' => ['nullable', 'string', 'max:500'],
            'gcash_number' => ['nullable', 'string', 'max:20'],
            'plan'    => ['required', 'in:free,basic,pro'],
        ];
    }
}
