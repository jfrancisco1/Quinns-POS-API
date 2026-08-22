<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ExpenseService extends BaseService
{
    public function __construct(
        private readonly ExpenseCategoryService $expenseCategoryService
    ) {
    }

    protected function model(): string
    {
        return Expense::class;
    }

    public function getAll(?int $branchId = null, ?int $categoryId = null): Collection
    {
        $query = $this->tenantScope()->with('category')->latest('expense_date');

        if ($branchId && Auth::user()?->role === 'admin') {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        return $query->get();
    }

    public function create(array $data): Expense
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $payload = [
            ...$data,
            'user_id'   => $user->id ?? 1,
            'tenant_id' => $tenantId,
            'branch_id' => $user->branch_id ?? $data['branch_id'] ?? 1,
        ];

        // Temporary compatibility fallback: older API clients (e.g. the staff app)
        // predate expense_category_id becoming required. Remove once they're updated.
        if (empty($payload['expense_category_id'])) {
            $payload['expense_category_id'] = $this->expenseCategoryService
                ->findOrCreateUncategorized($tenantId)->id;
        }

        // Idempotent: client-generated UUID prevents duplicates on re-sync
        if (!empty($payload['id'])) {
            $expense = Expense::updateOrCreate(['id' => $payload['id']], $payload);
        } else {
            $expense = Expense::create($payload);
        }

        return $expense->load('category');
    }

    public function update(Expense $expense, array $data): Expense
    {
        $this->authorizeTenant($expense);

        $expense->update($data);
        return $expense->fresh()->load('category');
    }

    public function delete(Expense $expense): void
    {
        $this->authorizeTenant($expense);
        $expense->delete();
    }
}
