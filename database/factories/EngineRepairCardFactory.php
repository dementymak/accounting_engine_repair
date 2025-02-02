<?php

namespace Database\Factories;

use App\Models\EngineRepairCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class EngineRepairCardFactory extends Factory
{
    protected $model = EngineRepairCard::class;

    public function definition()
    {
        return [
            'task_number' => 'TASK-' . $this->faker->unique()->randomNumber(5),
            'repair_card_number' => 'RC-' . $this->faker->unique()->randomNumber(5),
            'model' => $this->faker->word,
            'temperature_sensor' => $this->faker->word,
            'crown_height' => $this->faker->randomFloat(2, 5, 20),
            'connection_type' => $this->faker->randomElement(['serial', 'parallel']),
            'connection_notes' => $this->faker->sentence,
            'groove_distances' => [$this->faker->randomFloat(2, 5, 15), $this->faker->randomFloat(2, 5, 15)],
            'wires_in_groove' => $this->faker->numberBetween(1, 10),
            'scrap_weight' => $this->faker->randomFloat(2, 0, 10),
            'total_wire_weight' => $this->faker->randomFloat(2, 10, 50),
            'winding_resistance' => $this->faker->randomFloat(2, 0.1, 2.0) . ' Ohm',
            'mass_resistance' => $this->faker->randomFloat(2, 0.1, 2.0) . ' Ohm',
            'notes' => $this->faker->paragraph,
            'completed_at' => $this->faker->optional()->dateTime(),
        ];
    }
} 