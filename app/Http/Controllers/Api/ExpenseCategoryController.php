<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        private readonly ExpenseCategoryService $expenseCategoryService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return ExpenseCategoryResource::collection($this->expenseCategoryService->getAll());
    }

    public function store(StoreExpenseCategoryRequest $request): ExpenseCategoryResource
    {
        $expenseCategory = $this->expenseCategoryService->create($request->validated());
        return new ExpenseCategoryResource($expenseCategory);
    }

    public function show(ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        return new ExpenseCategoryResource($expenseCategory);
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        $expenseCategory = $this->expenseCategoryService->update($expenseCategory, $request->validated());
        return new ExpenseCategoryResource($expenseCategory);
    }

    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->expenseCategoryService->delete($expenseCategory);
        return response()->json(['message' => 'Expense category deleted successfully']);
    }
}
