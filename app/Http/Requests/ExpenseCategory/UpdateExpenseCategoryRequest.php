<?php

namespace App\Http\Requests\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = Auth::user()?->tenant_id ?? 1;

        return [
            'name'       => ['sometimes', 'string', 'max:255', Rule::unique('expense_categories', 'name')->where('tenant_id', $tenantId)->ignore($this->expense_category)],
            'is_active'  => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
