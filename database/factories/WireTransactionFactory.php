<?php

namespace Database\Factories;

use App\Models\WireTransaction;
use App\Models\WireInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class WireTransactionFactory extends Factory
{
    protected $model = WireTransaction::class;

    public function definition()
    {
        return [
            'wire_id' => WireInventory::factory(),
            'type' => $this->faker->randomElement(['income', 'expenditure']),
            'amount' => function (array $attributes) {
                return $attributes['type'] === 'income' 
                    ? $this->faker->randomFloat(2, 1, 20)
                    : -$this->faker->randomFloat(2, 1, 20);
            },
            'notes' => $this->faker->sentence,
        ];
    }
} 