<?php

namespace Database\Factories;

use App\Models\WireInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class WireInventoryFactory extends Factory
{
    protected $model = WireInventory::class;

    public function definition()
    {
        return [
            'diameter' => $this->faker->unique()->randomFloat(2, 0.5, 2.0),
            'weight' => $this->faker->randomFloat(2, 5, 50),
        ];
    }
} 