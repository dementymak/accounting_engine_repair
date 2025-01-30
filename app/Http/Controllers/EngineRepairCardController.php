<?php

namespace App\Http\Controllers;

use App\Models\EngineRepairCard;
use App\Models\WireInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ScrapInventory;
use App\Models\ScrapTransaction;
use App\Models\WireTransaction;

class EngineRepairCardController extends Controller
{
    public function index()
    {
        $repairCards = EngineRepairCard::with(['wireUsages.wireInventory', 'originalWires'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $wires = WireInventory::all();
        
        return view('repair-cards.index', compact('repairCards', 'wires'));
    }

    public function create()
    {
        $wires = WireInventory::all();
        return view('repair-cards.create', compact('wires'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            // Create repair card
            $repairCard = EngineRepairCard::create($data);

            // Handle original wires
            if ($request->has('original_wires')) {
                foreach ($request->original_wires as $wire) {
                    if (!empty($wire['diameter']) && !empty($wire['wire_count'])) {
                        $repairCard->originalWires()->create($wire);
                    }
                }
            }

            // Handle wire usage
            if ($request->has('wire_usage')) {
                foreach ($request->wire_usage as $usage) {
                    if (!empty($usage['wire_inventory_id']) && isset($usage['used_weight'])) {
                        $wireInventory = WireInventory::findOrFail($usage['wire_inventory_id']);
                        $initialWeight = $wireInventory->weight;
                        
                        // Create wire usage record
                        $wireUsage = $repairCard->wireUsages()->create([
                            'wire_inventory_id' => $usage['wire_inventory_id'],
                            'initial_weight' => $initialWeight,
                            'used_weight' => $usage['used_weight']
                        ]);

                        // Update wire inventory
                        $consumedWeight = $initialWeight - $usage['used_weight'];
                        $wireInventory->decrement('weight', $consumedWeight);

                        // Create wire transaction
                        WireTransaction::create([
                            'wire_id' => $usage['wire_inventory_id'],
                            'repair_card_id' => $repairCard->id,
                            'type' => 'expenditure',
                            'amount' => -$consumedWeight,
                        ]);
                    }
                }
            }

            // Handle scrap weight
            if ($request->filled('scrap_weight')) {
                $scrapInventory = ScrapInventory::firstOrCreate(['id' => 1], ['weight' => 0]);
                $scrapInventory->increment('weight', $request->scrap_weight);
                
                // Create scrap transaction
                ScrapTransaction::create([
                    'type' => 'repair_card',
                    'weight' => $request->scrap_weight,
                    'repair_card_id' => $repairCard->id,
                    'notes' => 'Added from repair card #' . $repairCard->repair_card_number,
                ]);
            }

            DB::commit();
            return redirect()->route('repair-cards.index')
                ->with('success', 'Repair card created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating repair card: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(EngineRepairCard $repairCard)
    {
        $wires = WireInventory::all();
        $repairCard->load(['wireUsages.wireInventory', 'originalWires']);
        return view('repair-cards.edit', compact('repairCard', 'wires'));
    }

    public function update(Request $request, EngineRepairCard $repairCard)
    {
        $data = $this->validateRequest($request, $repairCard);

        DB::beginTransaction();
        try {
            // Handle completion status
            if ($request->has('completed') && !$repairCard->completed_at) {
                $data['completed_at'] = now();
            } elseif (!$request->has('completed')) {
                $data['completed_at'] = null;
            }

            // Update repair card
            $repairCard->update($data);

            // Handle scrap weight update
            if ($request->filled('scrap_weight')) {
                $scrapInventory = ScrapInventory::firstOrCreate(['id' => 1], ['weight' => 0]);
                $oldScrapWeight = $repairCard->scrap_weight;

                if ($oldScrapWeight != $request->scrap_weight) {
                    // Update the inventory total
                    if ($oldScrapWeight) {
                        $scrapInventory->decrement('weight', $oldScrapWeight);
                    }
                    $scrapInventory->increment('weight', $request->scrap_weight);
                    
                    // Create a single transaction for the update
                    ScrapTransaction::create([
                        'type' => 'repair_card',
                        'weight' => $request->scrap_weight,
                        'repair_card_id' => $repairCard->id,
                        'notes' => 'Updated scrap weight for repair card #' . $repairCard->repair_card_number,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('repair-cards.index')
                ->with('success', 'Repair card updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating repair card: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function toggleComplete(EngineRepairCard $repairCard)
    {
        try {
            if ($repairCard->completed_at) {
                $repairCard->update(['completed_at' => null]);
                $message = 'Repair card marked as incomplete.';
            } else {
                $repairCard->update(['completed_at' => now()]);
                $message = 'Repair card marked as completed.';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating completion status: ' . $e->getMessage());
        }
    }

    public function destroy(EngineRepairCard $repairCard)
    {
        DB::beginTransaction();
        try {
            // Restore wire inventory amounts
            foreach ($repairCard->wireUsages as $usage) {
                $wireInventory = $usage->wireInventory;
                $wireInventory->update([
                    'weight' => $wireInventory->weight + ($usage->initial_weight - $usage->used_weight)
                ]);
            }

            $repairCard->delete();
            DB::commit();

            return redirect()->route('repair-cards.index')
                ->with('success', 'Repair card deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting repair card: ' . $e->getMessage());
        }
    }

    protected function validateRequest(Request $request, ?EngineRepairCard $repairCard = null): array
    {
        $validated = $request->validate([
            'task_number' => 'required|string|max:255',
            'repair_card_number' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'temperature_sensor' => 'nullable|string|max:255',
            'crown_height' => 'nullable|numeric|min:0',
            'connection_type' => 'nullable|in:serial,parallel',
            'connection_notes' => 'nullable|string',
            'groove_distances' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $values = array_map('trim', explode('/', $value));
                    foreach ($values as $val) {
                        if (!is_numeric($val) || $val < 0) {
                            $fail(__('messages.invalid_groove_distances'));
                        }
                    }
                }
            }],
            'wires_in_groove' => 'nullable|integer|min:1',
            'wire' => 'nullable|string',
            'scrap_weight' => 'nullable|numeric|min:0',
            'total_wire_weight' => 'nullable|numeric|min:0',
            'winding_resistance' => 'nullable|string',
            'mass_resistance' => 'nullable|string',
            'notes' => 'nullable|string',
            'completed_at' => 'nullable|date',
        ]);

        // Convert groove_distances from string to array if present
        if (!empty($validated['groove_distances'])) {
            $validated['groove_distances'] = array_map(
                function ($value) {
                    return trim($value);
                },
                explode('/', $validated['groove_distances'])
            );
        }

        return $validated;
    }
} 

