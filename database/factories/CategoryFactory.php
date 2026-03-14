<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            ['name' => 'Wash & Dry',     'color' => '#3B82F6'],
            ['name' => 'Wash & Fold',    'color' => '#10B981'],
            ['name' => 'Dry Clean',      'color' => '#8B5CF6'],
            ['name' => 'Iron / Press',   'color' => '#F59E0B'],
            ['name' => 'Comforter',      'color' => '#EF4444'],
            ['name' => 'Bedsheets',      'color' => '#EC4899'],
            ['name' => 'Delivery',       'color' => '#6366F1'],
        ];

        $pick = $this->faker->unique()->randomElement($categories);

        return [
            'name'      => $pick['name'],
            'color'     => $pick['color'],
            'is_active' => true,
        ];
    }
}
