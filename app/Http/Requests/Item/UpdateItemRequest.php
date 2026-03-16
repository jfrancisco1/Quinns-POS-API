<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color'       => ['nullable', 'string', 'max:50'],
            'shape'       => ['nullable', 'string', 'in:circle,square,rectangle,hexagon,diamond'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'cost'        => ['sometimes', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
