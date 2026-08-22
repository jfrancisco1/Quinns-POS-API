<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => $this->faker->unique()->randomElement(ExpenseCategory::DEFAULT_NAMES),
            'is_active' => true,
        ];
    }
}
