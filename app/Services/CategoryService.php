<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CategoryService extends BaseService
{
    protected function model(): string
    {
        return Category::class;
    }

    public function getAll(): Collection
    {
        $user = Auth::user();
        $tenant_id = $user?->tenant_id ?? 1;

        return Category::where('tenant_id', $tenant_id)->latest()->get();
    }

    public function create(array $data): Category
    {
        $user = Auth::user();

        return Category::create([
            ...$data,
            'tenant_id' => $user->tenant_id ?? 1,
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $this->authorizeTenant($category);

        $category->update($data);
        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $this->authorizeTenant($category);

        $category->delete();
    }
}
