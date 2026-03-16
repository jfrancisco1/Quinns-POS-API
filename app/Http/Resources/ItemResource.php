<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'color'       => $this->color,
            'shape'       => $this->shape,
            'price'       => $this->price,
            'cost'        => $this->cost,
            'is_active'   => $this->is_active,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
