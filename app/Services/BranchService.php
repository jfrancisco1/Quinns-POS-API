<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BranchService
{
    public function getAllForTenant(Tenant $tenant): Collection
    {
        return $tenant->branches()->withCount('users')->latest()->get();
    }

    public function findById(Tenant $tenant, int $branchId): Branch
    {
        return $tenant->branches()->withCount('users')->findOrFail($branchId);
    }

    public function create(Tenant $tenant, array $data): Branch
    {
        return $tenant->branches()->create($data);
    }

    public function update(Branch $branch, array $data): Branch
    {
        $branch->update($data);

        return $branch->fresh();
    }

    public function delete(Branch $branch): void
    {
        $branch->delete();
    }

    /**
     * Splits the source branch's items into those to copy and those skipped because an
     * item with the same name (case-insensitive) already exists in the target branch.
     *
     * @return array{copy: \Illuminate\Support\Collection<int, Item>, skip: \Illuminate\Support\Collection<int, Item>}
     */
    public function planItemCopy(Branch $source, Branch $target): array
    {
        $existing = Item::where('branch_id', $target->id)
            ->pluck('name')
            ->map(fn (string $name) => mb_strtolower($name))
            ->flip();

        [$skip, $copy] = Item::where('branch_id', $source->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->partition(fn (Item $item) => $existing->has(mb_strtolower($item->name)));

        return ['copy' => $copy->values(), 'skip' => $skip->values()];
    }

    /**
     * Copies the source branch's items into the target branch. Items whose name already
     * exists in the target are skipped, so the call is safe to repeat. Categories and
     * expense categories are tenant-wide, so every branch already shares them.
     *
     * @return array{copied: int, skipped: int}
     */
    public function copyItems(Branch $source, Branch $target): array
    {
        return DB::transaction(function () use ($source, $target) {
            $plan = $this->planItemCopy($source, $target);

            foreach ($plan['copy'] as $item) {
                $copy = $item->replicate();
                $copy->branch_id = $target->id;
                $copy->save();
            }

            return ['copied' => $plan['copy']->count(), 'skipped' => $plan['skip']->count()];
        });
    }
}
