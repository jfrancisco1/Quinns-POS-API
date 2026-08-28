<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nickname'     => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'mobile'       => '09' . $this->faker->numerify('#########'),
            'address'      => $this->faker->address(),
            'latitude'     => $this->faker->optional(0.7)->latitude(),
            'longitude'    => $this->faker->optional(0.7)->longitude(),
            'notes'        => $this->faker->optional(0.4)->sentence(),
            'delivery_fee' => $this->faker->randomElement([0, 30, 50, 80, 100]),
            'tenant_id'    => null, // set by seeder
            'branch_id'    => null, // set by seeder
        ];
    }
}
