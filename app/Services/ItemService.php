<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ItemService extends BaseService
{
    protected function model(): string
    {
        return Item::class;
    }

    public function getAll(): Collection
    {
        return $this->tenantScope()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): Item
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        if (!isset($data['sort_order'])) {
            $max = Item::where('tenant_id', $tenantId)->max('sort_order');
            $data['sort_order'] = ($max ?? -1) + 1;
        }

        return Item::create([
            ...$data,
            'tenant_id' => $tenantId,
            'branch_id' => $user->branch_id ?? $data['branch_id'] ?? 1,
        ]);
    }

    public function update(Item $item, array $data): Item
    {
        $this->authorizeTenant($item);

        $item->update($data);
        return $item->fresh();
    }

    public function delete(Item $item): void
    {
        $this->authorizeTenant($item);

        $item->delete();
    }
}
