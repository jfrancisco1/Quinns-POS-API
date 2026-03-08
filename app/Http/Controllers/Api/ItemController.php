<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemService $itemService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return ItemResource::collection($this->itemService->getAll());
    }

    public function store(StoreItemRequest $request): ItemResource
    {
        $item = $this->itemService->create($request->validated());
        return new ItemResource($item);
    }

    public function show(Item $item): ItemResource
    {
        return new ItemResource($item->load('category'));
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $item = $this->itemService->update($item, $request->validated());
        return new ItemResource($item);
    }

    public function destroy(Item $item): JsonResponse
    {
        $this->itemService->delete($item);
        return response()->json(['message' => 'Item deleted successfully']);
    }
}
