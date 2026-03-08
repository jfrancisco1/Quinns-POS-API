<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categoryService->getAll());
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $category = $this->categoryService->create($request->validated());
        return new CategoryResource($category);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category = $this->categoryService->update($category, $request->validated());
        return new CategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
