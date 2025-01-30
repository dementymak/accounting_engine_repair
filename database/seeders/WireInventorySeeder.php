<?php

namespace Database\Seeders;

use App\Models\WireInventory;
use Illuminate\Database\Seeder;

class WireInventorySeeder extends Seeder
{
    public function run(): void
    {
        $wires = [
            [
                'diameter' => 0.65,
                'weight' => 15.36,
            ],
            [
                'diameter' => 0.70,
                'weight' => 14.38,
            ],
            [
                'diameter' => 0.75,
                'weight' => 24.00,
            ],
        ];

        foreach ($wires as $wire) {
            WireInventory::create($wire);
        }
    }
} 