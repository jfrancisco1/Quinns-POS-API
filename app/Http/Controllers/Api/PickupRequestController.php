<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PickupRequest\StorePickupRequestRequest;
use App\Http\Requests\PickupRequest\UpdatePickupRequestRequest;
use App\Http\Resources\PickupRequestResource;
use App\Models\PickupRequest;
use App\Services\PickupRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PickupRequestController extends Controller
{
    public function __construct(
        private readonly PickupRequestService $pickupRequestService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $statuses = array_filter(explode(',', (string) $request->query('status')));
        $date     = $request->date('date')?->toDateString();
        $branchId = $request->integer('branch_id') ?: null;

        return PickupRequestResource::collection(
            $this->pickupRequestService->getAll($statuses ?: null, $date, $branchId)
        );
    }

    public function store(StorePickupRequestRequest $request): PickupRequestResource
    {
        return new PickupRequestResource($this->pickupRequestService->create($request->validated()));
    }

    public function show(PickupRequest $pickupRequest): PickupRequestResource
    {
        return new PickupRequestResource($pickupRequest->load(['branch', 'assignedDriver']));
    }

    public function update(UpdatePickupRequestRequest $request, PickupRequest $pickupRequest): PickupRequestResource
    {
        return new PickupRequestResource($this->pickupRequestService->update($pickupRequest, $request->validated()));
    }
}
