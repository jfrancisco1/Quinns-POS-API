<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        $descriptions = [
            'Detergent supplies',
            'Fabric softener',
            'Electricity bill',
            'Water bill',
            'Equipment maintenance',
            'Plastic bags',
            'Staff overtime',
            'Delivery gas',
            'Packaging materials',
            'Cleaning supplies',
        ];

        return [
            'description'         => $this->faker->randomElement($descriptions),
            'amount'              => $this->faker->randomFloat(2, 100, 3000),
            'expense_date'        => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'note'                => $this->faker->optional(0.4)->sentence(),
            'user_id'             => null, // set by seeder
            'tenant_id'           => null, // set by seeder
            'branch_id'           => null, // set by seeder
            'expense_category_id' => null, // set by seeder
        ];
    }
}
