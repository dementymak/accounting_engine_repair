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
        // Log the incoming request data
        Log::info('Repair card store request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'number' => 'required|integer|unique:engine_repair_cards,number',
            'crown_height' => 'nullable|numeric',
            'connection_type' => 'nullable|in:serial,parallel',
            'connection_notes' => 'nullable|string',
            'groove_distances' => 'nullable|string',
            'wires_in_groove' => 'nullable|integer',
            'temperature_sensor' => 'nullable|string',
            'scrap_weight' => 'nullable|numeric|min:0',
            'model' => 'nullable|string|max:100',
            'winding_resistance' => 'nullable|string',
            'mass_resistance' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'original_wires.*.diameter' => 'nullable|numeric',
            'original_wires.*.wire_count' => 'nullable|integer',
            'wire_usage.*.wire_inventory_id' => 'nullable|exists:wire_inventory,id',
            'wire_usage.*.used_weight' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create repair card with only fillable fields
            $repairCardData = $request->only([
                'number',
                'crown_height',
                'connection_type',
                'connection_notes',
                'wires_in_groove',
                'temperature_sensor',
                'scrap_weight',
                'winding_resistance',
                'mass_resistance',
                'model',
                'notes',
            ]);

            // Handle groove distances
            if ($request->has('groove_distances')) {
                $repairCardData['groove_distances'] = array_map('trim', explode('/', $request->groove_distances));
            }

            Log::info('Creating repair card with data:', $repairCardData);
            $repairCard = EngineRepairCard::create($repairCardData);

            // Store original wires
            if ($request->has('original_wires')) {
                foreach ($request->original_wires as $wire) {
                    if (!empty($wire['diameter']) && !empty($wire['wire_count'])) {
                        Log::info('Creating original wire:', $wire);
                        $repairCard->originalWires()->create($wire);
                    }
                }
            }

            // Store wire usage
            if ($request->has('wire_usage')) {
                $totalWireWeight = 0;
                foreach ($request->wire_usage as $usage) {
                    if (!empty($usage['wire_inventory_id']) && isset($usage['used_weight'])) {
                        $wireInventory = WireInventory::find($usage['wire_inventory_id']);
                        if (!$wireInventory) {
                            throw new \Exception('Wire inventory not found: ' . $usage['wire_inventory_id']);
                        }

                        $initialWeight = $wireInventory->weight;
                        $residualWeight = $usage['used_weight'];
                        $consumedWeight = $initialWeight - $residualWeight;

                        if ($consumedWeight < 0) {
                            throw new \Exception('Invalid residual weight: cannot be greater than initial weight');
                        }
                        
                        Log::info('Creating wire usage:', [
                            'wire_inventory_id' => $usage['wire_inventory_id'],
                            'initial_weight' => $initialWeight,
                            'residual_weight' => $residualWeight,
                            'consumed_weight' => $consumedWeight
                        ]);

                        // Create wire usage record
                        $repairCard->wireUsages()->create([
                            'wire_inventory_id' => $usage['wire_inventory_id'],
                            'initial_weight' => $initialWeight,
                            'used_weight' => $residualWeight,
                        ]);

                        // Update wire inventory with the residual weight
                        $wireInventory->update(['weight' => $residualWeight]);

                        $totalWireWeight += $consumedWeight;
                    }
                }

                // Update total wire weight
                $repairCard->update(['total_wire_weight' => $totalWireWeight]);
            }

            // Handle scrap weight
            if ($request->filled('scrap_weight')) {
                $scrapInventory = ScrapInventory::firstOrCreate(
                    [],
                    ['weight' => 0]
                );

                ScrapTransaction::create([
                    'type' => 'repair_card',
                    'weight' => $request->scrap_weight,
                    'repair_card_id' => $repairCard->id,
                    'notes' => 'Added from repair card #' . $repairCard->number,
                ]);

                $scrapInventory->increment('weight', $request->scrap_weight);
            }

            DB::commit();
            Log::info('Repair card created successfully with ID: ' . $repairCard->id);

            return redirect()->route('repair-cards.index')
                ->with('success', 'Repair card created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating repair card: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

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
        $validator = Validator::make($request->all(), [
            'number' => 'required|integer|unique:engine_repair_cards,number,' . $repairCard->id,
            'crown_height' => 'nullable|numeric',
            'connection_type' => 'nullable|in:serial,parallel',
            'wires_in_groove' => 'nullable|integer',
            'scrap_weight' => 'nullable|numeric|min:0',
            'model' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'original_wires.*.diameter' => 'nullable|numeric',
            'original_wires.*.wire_count' => 'nullable|integer',
            'wire_usage.*.wire_inventory_id' => 'nullable|exists:wire_inventory,id',
            'wire_usage.*.used_weight' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Get the old scrap weight before updating
            $oldScrapWeight = $repairCard->scrap_weight;

            // Update repair card
            $repairCard->update($request->all());

            if ($request->has('completed')) {
                $repairCard->update(['completed_at' => now()]);
            }

            // Update original wires
            $repairCard->originalWires()->delete();
            if ($request->has('original_wires')) {
                foreach ($request->original_wires as $wire) {
                    if (!empty($wire['diameter']) && !empty($wire['wire_count'])) {
                        $repairCard->originalWires()->create($wire);
                    }
                }
            }

            // Update wire usage
            if ($request->has('wire_usage')) {
                // Restore previous inventory amounts
                foreach ($repairCard->wireUsages as $oldUsage) {
                    $wireInventory = $oldUsage->wireInventory;
                    $wireInventory->update([
                        'weight' => $wireInventory->weight + ($oldUsage->initial_weight - $oldUsage->used_weight)
                    ]);
                }

                $repairCard->wireUsages()->delete();

                // Create new usage records and update inventory
                $totalWireWeight = 0;
                foreach ($request->wire_usage as $usage) {
                    if (!empty($usage['wire_inventory_id']) && isset($usage['used_weight'])) {
                        $wireInventory = WireInventory::find($usage['wire_inventory_id']);
                        $initialWeight = $wireInventory->weight;
                        $residualWeight = $usage['used_weight'];
                        $consumedWeight = $initialWeight - $residualWeight;

                        if ($consumedWeight < 0) {
                            throw new \Exception('Invalid residual weight: cannot be greater than initial weight');
                        }

                        // Create wire usage record
                        $repairCard->wireUsages()->create([
                            'wire_inventory_id' => $usage['wire_inventory_id'],
                            'initial_weight' => $initialWeight,
                            'used_weight' => $residualWeight,
                        ]);

                        // Update wire inventory
                        $wireInventory->update(['weight' => $residualWeight]);

                        $totalWireWeight += $consumedWeight;
                    }
                }

                // Update total wire weight
                $repairCard->update(['total_wire_weight' => $totalWireWeight]);
            }

            // Handle scrap weight changes
            if ($request->filled('scrap_weight') && $request->scrap_weight != $oldScrapWeight) {
                $scrapInventory = ScrapInventory::firstOrCreate(
                    [],
                    ['weight' => 0]
                );

                // If there was an old scrap weight, subtract it first
                if ($oldScrapWeight) {
                    $scrapInventory->decrement('weight', $oldScrapWeight);
                    
                    // Create transaction for removing old scrap weight
                    ScrapTransaction::create([
                        'type' => 'writeoff',
                        'weight' => -$oldScrapWeight,
                        'repair_card_id' => $repairCard->id,
                        'notes' => 'Removed from repair card #' . $repairCard->number . ' during update',
                    ]);
                }

                // Add new scrap weight
                $scrapInventory->increment('weight', $request->scrap_weight);
                
                // Create transaction for new scrap weight
                ScrapTransaction::create([
                    'type' => 'repair_card',
                    'weight' => $request->scrap_weight,
                    'repair_card_id' => $repairCard->id,
                    'notes' => 'Updated from repair card #' . $repairCard->number,
                ]);
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
} 

