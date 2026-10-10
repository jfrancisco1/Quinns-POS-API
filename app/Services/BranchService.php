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
     * Copies the source branch's items into the target branch. Items whose name already
     * exists in the target are skipped, so the call is safe to repeat. Categories and
     * expense categories are tenant-wide, so every branch already shares them.
     *
     * @return array{copied: int, skipped: int}
     */
    public function copyItems(Branch $source, Branch $target): array
    {
        return DB::transaction(function () use ($source, $target) {
            $existing = Item::where('branch_id', $target->id)
                ->pluck('name')
                ->map(fn (string $name) => mb_strtolower($name))
                ->flip();

            $copied = 0;
            $skipped = 0;

            Item::where('branch_id', $source->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->each(function (Item $item) use ($target, $existing, &$copied, &$skipped) {
                    if ($existing->has(mb_strtolower($item->name))) {
                        $skipped++;

                        return;
                    }

                    $copy = $item->replicate();
                    $copy->branch_id = $target->id;
                    $copy->save();
                    $copied++;
                });

            return ['copied' => $copied, 'skipped' => $skipped];
        });
    }
}
