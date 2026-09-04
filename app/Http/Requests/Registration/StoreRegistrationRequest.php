<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:tenants,email'],
            'phone'         => ['nullable', 'string', 'max:20', 'unique:tenants,phone'],
            'address'       => ['nullable', 'string', 'max:500'],
            'owner_name'    => ['required', 'string', 'max:255'],
        ];
    }
}
