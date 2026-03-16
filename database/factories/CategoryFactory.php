<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            'Wash & Dry',
            'Wash & Fold',
            'Dry Clean',
            'Iron / Press',
            'Comforter',
            'Bedsheets',
            'Delivery',
        ];

        return [
            'name'      => $this->faker->unique()->randomElement($categories),
            'is_active' => true,
        ];
    }
}
