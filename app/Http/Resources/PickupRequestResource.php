<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'branch'         => $this->whenLoaded('branch', fn() => ['id' => $this->branch->id, 'name' => $this->branch->name]),
            'customerId'     => $this->customer_id,
            'customerName'   => $this->customer_name,
            'mobile'         => $this->mobile,
            'address'        => $this->address,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'notes'          => $this->notes,
            'scheduledFor'   => $this->scheduled_for?->toDateString(),
            'status'         => $this->status,
            'source'         => $this->source,
            'assignedDriver' => $this->whenLoaded('assignedDriver', fn() => $this->assignedDriver
                ? ['id' => $this->assignedDriver->id, 'name' => $this->assignedDriver->name]
                : null),
            'createdBy'      => $this->created_by,
            'bagCount'       => $this->bag_count,
            'assignedAt'     => $this->assigned_at?->toISOString(),
            'pickedUpAt'     => $this->picked_up_at?->toISOString(),
            'receivedAt'     => $this->received_at?->toISOString(),
            'cancelledAt'    => $this->cancelled_at?->toISOString(),
            'createdAt'      => $this->created_at?->toISOString(),
        ];
    }
}
