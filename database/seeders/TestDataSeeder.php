<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EngineRepairCard;
use App\Models\WireInventory;
use App\Models\WireUsage;
use App\Models\ScrapInventory;
use App\Models\ScrapTransaction;
use App\Models\OriginalWire;
use App\Models\WireTransaction;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create wire inventory items
        $wires = [
            [
                'diameter' => 0.5,
                'weight' => 100.0,
                'notes' => 'Thin copper wire'
            ],
            [
                'diameter' => 0.8,
                'weight' => 150.0,
                'notes' => 'Medium copper wire'
            ],
            [
                'diameter' => 1.2,
                'weight' => 200.0,
                'notes' => 'Thick copper wire'
            ]
        ];

        foreach ($wires as $wire) {
            WireInventory::create($wire);
        }

        // Initialize scrap inventory
        $scrapInventory = ScrapInventory::create([
            'id' => 1,
            'weight' => 0
        ]);

        // Create repair cards
        for ($i = 1; $i <= 17; $i++) {
            $repairCard = EngineRepairCard::create([
                'task_number' => 'TASK-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'repair_card_number' => 'RC-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'model' => 'Model ' . chr(65 + (($i-1) % 26)),
                'crown_height' => rand(10, 50) / 10,
                'connection_type' => ['serial', 'parallel'][rand(0, 1)],
                'connection_notes' => 'Test connection notes ' . $i,
                'temperature_sensor' => rand(0, 1) ? 'Type ' . chr(65 + (($i-1) % 3)) : null,
                'groove_distances' => [rand(5, 15), rand(5, 15), rand(5, 15)],
                'wires_in_groove' => rand(1, 5),
                'scrap_weight' => rand(10, 50) / 10,
                'winding_resistance' => rand(100, 999) / 100,
                'mass_resistance' => rand(100, 999) / 100,
                'notes' => 'Test notes for repair card ' . $i,
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Add original wires (at least 1, up to 3)
            $originalWireCount = rand(1, 3);
            for ($j = 0; $j < $originalWireCount; $j++) {
                OriginalWire::create([
                    'repair_card_id' => $repairCard->id,
                    'diameter' => rand(10, 30) / 10,
                    'wire_count' => rand(1, 10),
                ]);
            }

            // Add wire usages (at least 1, up to 3)
            $wireUsageCount = rand(1, 3);
            $availableWires = WireInventory::inRandomOrder()->limit($wireUsageCount)->get();
            foreach ($availableWires as $wire) {
                // Find the last usage of this wire
                $lastUsage = WireUsage::where('wire_inventory_id', $wire->id)
                    ->whereNotNull('completed_at')
                    ->latest()
                    ->first();

                // Calculate initial weight based on previous usage or current wire weight
                $initialWeight = $lastUsage ? $lastUsage->used_weight : $wire->weight;
                $usedWeight = rand(10, 50) / 10; // Random weight between 1-5 kg
                $residualWeight = $initialWeight - $usedWeight;
                
                // Create wire usage record
                $usage = WireUsage::create([
                    'repair_card_id' => $repairCard->id,
                    'previous_repair_card_id' => $lastUsage ? $lastUsage->repair_card_id : null,
                    'wire_inventory_id' => $wire->id,
                    'initial_weight' => $initialWeight,
                    'used_weight' => $residualWeight,
                    'completed_at' => rand(0, 1) ? now() : null
                ]);

                // Create wire transaction for this usage
                WireTransaction::create([
                    'wire_id' => $wire->id,
                    'repair_card_id' => $repairCard->id,
                    'type' => 'expenditure',
                    'amount' => -$usedWeight,
                    'notes' => "Used for repair card #{$repairCard->repair_card_number}"
                ]);

                // Update wire inventory
                $wire->decrement('weight', $usedWeight);
            }

            // Update total wire weight
            $repairCard->total_wire_weight = $repairCard->calculateTotalUsedWeight();
            $repairCard->save();

            // Create scrap transaction
            ScrapTransaction::create([
                'type' => 'repair_card',
                'weight' => $repairCard->scrap_weight,
                'repair_card_id' => $repairCard->id,
                'notes' => 'Scrap from repair card #' . $repairCard->repair_card_number
            ]);

            // Update scrap inventory
            $scrapInventory->increment('weight', $repairCard->scrap_weight);
        }

        // Create some standalone scrap transactions
        $scrapTransactions = [
            [
                'type' => 'writeoff',
                'weight' => 5.0,
                'notes' => 'Monthly scrap writeoff'
            ],
            [
                'type' => 'initial',
                'weight' => 10.0,
                'notes' => 'Initial scrap balance'
            ]
        ];

        foreach ($scrapTransactions as $transaction) {
            ScrapTransaction::create($transaction);
            if ($transaction['type'] === 'initial') {
                $scrapInventory->increment('weight', $transaction['weight']);
            } else {
                $scrapInventory->decrement('weight', $transaction['weight']);
            }
        }
    }
} 