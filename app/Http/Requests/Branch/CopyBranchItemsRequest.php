<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyBranchItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_branch_id' => [
                'required',
                'integer',
                'different:branch',
                Rule::exists('branches', 'id')->where('tenant_id', $this->user()->tenant_id),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['branch' => $this->route('branch')?->id]);
    }

    public function messages(): array
    {
        return [
            'source_branch_id.different' => 'The source branch must be different from the target branch.',
        ];
    }
}
