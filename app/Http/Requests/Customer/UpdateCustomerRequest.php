<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255', "unique:customers,name,{$customerId}"],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', "unique:customers,phone,{$customerId}"],
            'email' => ['sometimes', 'nullable', 'email', "unique:customers,email,{$customerId}"],
            'address' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
