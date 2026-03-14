<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'price'       => $this->faker->randomElement([50, 80, 100, 120, 150, 180, 200, 250, 300]),
            'cost'        => $this->faker->randomElement([20, 30, 40, 50, 60, 80, 100]),
            'is_active'   => true,
            'category_id' => null, // set by seeder
            'tenant_id'   => null, // set by seeder
            'branch_id'   => null, // set by seeder
        ];
    }
}
