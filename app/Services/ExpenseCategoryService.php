<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryService extends BaseService
{
    protected function model(): string
    {
        return ExpenseCategory::class;
    }

    public function getAll(): Collection
    {
        $user = Auth::user();
        $tenant_id = $user?->tenant_id ?? 1;

        return ExpenseCategory::where('tenant_id', $tenant_id)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function create(array $data): ExpenseCategory
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        if (!isset($data['sort_order'])) {
            $max = ExpenseCategory::where('tenant_id', $tenantId)->max('sort_order');
            $data['sort_order'] = ($max ?? -1) + 1;
        }

        return ExpenseCategory::create([
            ...$data,
            'tenant_id' => $tenantId,
        ]);
    }

    public function update(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        $this->authorizeTenant($expenseCategory);

        $expenseCategory->update($data);
        return $expenseCategory->fresh();
    }

    public function delete(ExpenseCategory $expenseCategory): void
    {
        $this->authorizeTenant($expenseCategory);

        if ($expenseCategory->expenses()->exists()) {
            abort(422, 'Cannot delete a category that has expenses assigned to it.');
        }

        $expenseCategory->delete();
    }

    /** Provisions the default category set for a newly created tenant. */
    public function seedDefaults(int $tenantId): void
    {
        foreach (ExpenseCategory::DEFAULT_NAMES as $i => $name) {
            ExpenseCategory::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $name],
                ['is_active' => true, 'sort_order' => $i]
            );
        }
    }

    /**
     * Fallback for expenses created without a category (older API clients not yet
     * updated for the now-required field). Remove once all clients send one.
     */
    public function findOrCreateUncategorized(int $tenantId): ExpenseCategory
    {
        return ExpenseCategory::firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => ExpenseCategory::UNCATEGORIZED_NAME],
            ['is_active' => true, 'sort_order' => count(ExpenseCategory::DEFAULT_NAMES)]
        );
    }
}
