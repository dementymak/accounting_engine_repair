<?php

namespace Database\Factories;

use App\Models\ScrapInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScrapInventoryFactory extends Factory
{
    protected $model = ScrapInventory::class;

    public function definition()
    {
        return [
            'weight' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
} 