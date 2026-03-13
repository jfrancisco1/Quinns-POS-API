<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'username'   => $this->username,
            'role'       => $this->role,
            'is_active'  => $this->is_active,
            'tenant_id'  => $this->tenant_id,
            'branch_id'  => $this->branch_id,
            'branch'     => $this->whenLoaded('branch', fn() => [
                'id'   => $this->branch->id,
                'name' => $this->branch->name,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
