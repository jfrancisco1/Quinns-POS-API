<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $branchId = request()->integer('branch_id') ?: null;
        $categoryId = request()->integer('expense_category_id') ?: null;
        $expenses = $this->expenseService->getAll($branchId, $categoryId);
        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request): ExpenseResource
    {
        $expense = $this->expenseService->create($request->validated());
        return new ExpenseResource($expense);
    }

    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($expense->load('category'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense = $this->expenseService->update($expense, $request->validated());
        return new ExpenseResource($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->expenseService->delete($expense);
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
