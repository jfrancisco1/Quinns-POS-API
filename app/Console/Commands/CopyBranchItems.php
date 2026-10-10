<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Item;
use App\Services\BranchService;
use Illuminate\Console\Command;

class CopyBranchItems extends Command
{
    protected $signature = 'branches:copy-items
        {source : ID of the branch to copy items from}
        {target : ID of the branch to copy items into}
        {--apply : Actually copy the items (default is a dry run)}';

    protected $description = 'Copy all items from one branch into another branch of the same tenant, skipping names the target already has';

    public function handle(BranchService $branchService): int
    {
        $source = Branch::find($this->argument('source'));
        $target = Branch::find($this->argument('target'));

        if (! $source || ! $target) {
            $this->error('Branch not found: '.(! $source ? $this->argument('source') : $this->argument('target')));

            return self::FAILURE;
        }

        if ($source->id === $target->id) {
            $this->error('Source and target must be different branches.');

            return self::FAILURE;
        }

        if ($source->tenant_id !== $target->tenant_id) {
            $this->error('Source and target branches belong to different tenants.');

            return self::FAILURE;
        }

        $plan = $branchService->planItemCopy($source, $target);

        $this->info("From: #{$source->id} {$source->name}");
        $this->info("To:   #{$target->id} {$target->name}");

        if ($plan['skip']->isNotEmpty()) {
            $this->warn('Skipping '.$plan['skip']->count().' items already in the target: '.$plan['skip']->pluck('name')->implode(', '));
        }

        if ($plan['copy']->isEmpty()) {
            $this->info('No items to copy.');

            return self::SUCCESS;
        }

        $this->info('Items to copy: '.$plan['copy']->count());
        $this->table(
            ['id', 'name', 'price', 'cost', 'category_id', 'active'],
            $plan['copy']->map(fn (Item $item) => [
                $item->id,
                $item->name,
                $item->price,
                $item->cost,
                $item->category_id,
                $item->is_active ? 'yes' : 'no',
            ])->all()
        );

        if (! $this->option('apply')) {
            $this->warn('Dry run only — nothing written. Re-run with --apply to copy the items.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Copy '.$plan['copy']->count()." items into {$target->name} now?")) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        $result = $branchService->copyItems($source, $target);

        $this->info("Copied {$result['copied']} items into {$target->name}.");

        return self::SUCCESS;
    }
}
