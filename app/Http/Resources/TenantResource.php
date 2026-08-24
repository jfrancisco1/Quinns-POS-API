<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'gcash_number'  => $this->gcash_number,
            'plan'          => $this->plan,
            'is_active'     => $this->is_active,
            'branches_count' => $this->whenCounted('branches'),
            'users_count'   => $this->whenCounted('users'),
            'branches'      => BranchResource::collection($this->whenLoaded('branches')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
