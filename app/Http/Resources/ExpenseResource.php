<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'description'         => $this->description,
            'amount'              => $this->amount,
            'expense_date'        => $this->expense_date->toDateString(),
            'note'                => $this->note,
            'expense_category_id' => $this->expense_category_id,
            'category'            => new ExpenseCategoryResource($this->whenLoaded('category')),
            'user_id'             => $this->user_id,
            'created_at'          => $this->created_at->toDateTimeString(),
            'updated_at'          => $this->updated_at->toDateTimeString(),
        ];
    }
}
