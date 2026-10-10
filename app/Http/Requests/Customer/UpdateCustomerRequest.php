<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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
        return [
            'nickname' => ['sometimes', 'string', 'max:255'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:20', $this->uniqueMobileRule()],
            'address' => ['sometimes', 'nullable', 'string'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'delivery_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.unique' => 'A customer with this mobile number already exists.',
        ];
    }

    /** Mobile is unique per tenant, ignoring soft-deleted customers. */
    private function uniqueMobileRule(): Unique
    {
        return Rule::unique('customers', 'mobile')
            ->where('tenant_id', $this->user()?->tenant_id ?? 1)
            ->whereNull('deleted_at')
            ->ignore($this->route('customer')->id);
    }
}
