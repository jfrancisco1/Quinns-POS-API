<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ExpenseService extends BaseService
{
    protected function model(): string
    {
        return Expense::class;
    }

    public function getAll(?int $branchId = null): Collection
    {
        $query = $this->tenantScope()->latest('expense_date');

        if ($branchId && Auth::user()?->role === 'admin') {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    public function create(array $data): Expense
    {
        $user = Auth::user();

        $payload = [
            ...$data,
            'user_id'   => $user->id ?? 1,
            'tenant_id' => $user->tenant_id ?? 1,
            'branch_id' => $user->branch_id ?? $data['branch_id'] ?? 1,
        ];

        // Idempotent: client-generated UUID prevents duplicates on re-sync
        if (!empty($payload['id'])) {
            return Expense::updateOrCreate(['id' => $payload['id']], $payload);
        }

        return Expense::create($payload);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $this->authorizeTenant($expense);

        $expense->update($data);
        return $expense->fresh();
    }

    public function delete(Expense $expense): void
    {
        $this->authorizeTenant($expense);
        $expense->delete();
    }
}
