<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'         => ['required', 'string', 'max:255'],
            'amount'              => ['required', 'numeric', 'min:0'],
            'expense_date'        => ['required', 'date'],
            'note'                => ['nullable', 'string'],
            // Temporarily nullable for backward compatibility with clients created
            // before this field existed — ExpenseService falls back to Uncategorized.
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
        ];
    }
}
