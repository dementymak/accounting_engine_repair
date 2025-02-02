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
use App\Models\WireUsage;

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
        return view('repair-cards.form', compact('wires'));
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
                foreach ($request->original_wires as $originalWire) {
                    if (!empty($originalWire['diameter']) && !empty($originalWire['wire_count'])) {
                        $repairCard->originalWires()->create([
                            'diameter' => $originalWire['diameter'],
                            'wire_count' => $originalWire['wire_count']
                        ]);
                    }
                }
            }

            // Handle wire usage and reservations
            if ($request->has('wire_usage')) {
                foreach ($request->wire_usage as $usage) {
                    if (!empty($usage['wire_inventory_id']) && !empty($usage['used_weight'])) {
                        $wire = WireInventory::findOrFail($usage['wire_inventory_id']);
                        
                        // Find the last usage of this wire
                        $lastUsage = WireUsage::where('wire_inventory_id', $wire->id)
                            ->whereNotNull('completed_at')
                            ->latest()
                            ->first();

                        // Calculate initial weight based on previous usage or current wire weight
                        $initialWeight = $lastUsage ? $lastUsage->used_weight : $wire->weight;
                        
                        // Calculate consumed weight (initial - residual)
                        $consumedWeight = $initialWeight - $usage['used_weight'];
                        
                        // Check if there's enough available wire
                        if ($wire->availableWeight < $consumedWeight) {
                            throw new \Exception(__('messages.insufficient_stock'));
                        }

                        // Create wire usage record
                        $repairCard->wireUsages()->create([
                            'wire_inventory_id' => $wire->id,
                            'previous_repair_card_id' => $lastUsage ? $lastUsage->repair_card_id : null,
                            'initial_weight' => $initialWeight,
                            'used_weight' => $usage['used_weight']
                        ]);

                        // Create wire reservation
                        $repairCard->wireReservations()->create([
                            'wire_inventory_id' => $wire->id,
                            'reserved_weight' => $consumedWeight,
                            'initial_stock_weight' => $wire->weight
                        ]);

                        // Update wire inventory with consumed weight
                        $wire->decrement('weight', $consumedWeight);

                        // Create wire transaction
                        WireTransaction::create([
                            'wire_id' => $wire->id,
                            'repair_card_id' => $repairCard->id,
                            'type' => 'expenditure',
                            'amount' => -$consumedWeight,
                            'notes' => 'Used in repair card #' . $repairCard->repair_card_number
                        ]);
                    }
                }
            }

            // Update total wire weight
            $repairCard->total_wire_weight = $repairCard->calculateTotalUsedWeight();
            $repairCard->save();

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
                ->with('success', __('messages.repair_card_created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(EngineRepairCard $repairCard)
    {
        $wires = WireInventory::all();
        $repairCard->load(['wireUsages.wireInventory', 'originalWires']);
        return view('repair-cards.form', compact('repairCard', 'wires'));
    }

    public function update(Request $request, EngineRepairCard $repairCard)
    {
        $data = $this->validateRequest($request, $repairCard);

        DB::beginTransaction();
        try {
            \Log::info('Updating repair card', [
                'id' => $repairCard->id,
                'data' => $data,
                'original' => $repairCard->getOriginal()
            ]);

            // Update repair card basic info
            $repairCard->update($data);

            // Handle original wires
            $repairCard->originalWires()->delete();
            if ($request->has('original_wires')) {
                foreach ($request->original_wires as $originalWire) {
                    if (!empty($originalWire['diameter']) && !empty($originalWire['wire_count'])) {
                        $repairCard->originalWires()->create([
                            'diameter' => $originalWire['diameter'],
                            'wire_count' => $originalWire['wire_count']
                        ]);
                    }
                }
            }

            // Handle wire usage
            if ($request->has('wire_usage')) {
                // First, restore previously used wire amounts
                foreach ($repairCard->wireUsages as $oldUsage) {
                    if (!$oldUsage->completed_at) {
                        $consumedWeight = $oldUsage->initial_weight - $oldUsage->used_weight;
                        $oldUsage->wireInventory->increment('weight', $consumedWeight);
                        
                        // Delete old wire transactions
                        WireTransaction::where('repair_card_id', $repairCard->id)
                            ->where('wire_id', $oldUsage->wire_inventory_id)
                            ->delete();
                    }
                }
                
                // Remove old uncompleted records
                $repairCard->wireUsages()->whereNull('completed_at')->delete();
                $repairCard->wireReservations()->delete();

                // Create new wire usages and reservations
                foreach ($request->wire_usage as $usage) {
                    if (!empty($usage['wire_inventory_id']) && isset($usage['used_weight'])) {
                        $wire = WireInventory::findOrFail($usage['wire_inventory_id']);
                        
                        // Find the last completed usage of this wire
                        $lastUsage = $this->findLastCompletedUsage($wire->id, $repairCard->id);

                        // Calculate initial weight
                        $initialWeight = $lastUsage ? $lastUsage->used_weight : $wire->weight;
                        
                        // Calculate consumed weight
                        $consumedWeight = $initialWeight - floatval($usage['used_weight']);
                        
                        // Check if there's enough available wire
                        if ($wire->availableWeight < $consumedWeight) {
                            throw new \Exception(__('messages.insufficient_stock'));
                        }

                        // Create wire usage record
                        $repairCard->wireUsages()->create([
                            'wire_inventory_id' => $wire->id,
                            'previous_repair_card_id' => $lastUsage ? $lastUsage->repair_card_id : null,
                            'initial_weight' => $initialWeight,
                            'used_weight' => $usage['used_weight']
                        ]);

                        // Create wire reservation
                        $repairCard->wireReservations()->create([
                            'wire_inventory_id' => $wire->id,
                            'reserved_weight' => $consumedWeight,
                            'initial_stock_weight' => $wire->weight
                        ]);

                        // Update wire inventory
                        $wire->decrement('weight', $consumedWeight);

                        // Create wire transaction
                        WireTransaction::create([
                            'wire_id' => $wire->id,
                            'repair_card_id' => $repairCard->id,
                            'type' => 'expenditure',
                            'amount' => -$consumedWeight,
                            'notes' => 'Updated usage in repair card #' . $repairCard->repair_card_number
                        ]);
                    }
                }
            }

            // Update total wire weight
            $repairCard->total_wire_weight = $repairCard->calculateTotalUsedWeight();
            $repairCard->save();

            DB::commit();
            return redirect()->route('repair-cards.index')
                ->with('success', __('messages.repair_card_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed:', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function toggleComplete(EngineRepairCard $repairCard)
    {
        try {
            DB::beginTransaction();
            
            \Log::info('Starting toggle completion', [
                'repair_card_id' => $repairCard->id,
                'current_status' => $repairCard->completed_at ? 'completed' : 'not completed'
            ]);

            if ($repairCard->completed_at) {
                \Log::info('Uncompleting repair card', ['repair_card_id' => $repairCard->id]);
                
                // Uncomplete the repair card
                $repairCard->completed_at = null;
                $repairCard->wireUsages()->update(['completed_at' => null]);
                
                // Update wire transactions
                WireTransaction::where('repair_card_id', $repairCard->id)
                    ->update(['completed_at' => null]);
                    
                $message = __('messages.repair_card_uncompleted');
                
                \Log::info('Repair card uncompleted successfully', ['repair_card_id' => $repairCard->id]);
            } else {
                \Log::info('Completing repair card', ['repair_card_id' => $repairCard->id]);
                
                // Validate wire availability
                foreach ($repairCard->wireUsages as $usage) {
                    $wire = $usage->wireInventory;
                    $consumedWeight = $usage->initial_weight - $usage->used_weight;
                    
                    \Log::info('Checking wire availability', [
                        'wire_id' => $wire->id,
                        'diameter' => $wire->diameter,
                        'consumed_weight' => $consumedWeight,
                        'available_weight' => $wire->availableWeight
                    ]);
                    
                    if ($wire->availableWeight < $consumedWeight) {
                        throw new \Exception(__('messages.insufficient_stock_for_completion', [
                            'diameter' => $wire->diameter,
                            'required' => $consumedWeight,
                            'available' => $wire->availableWeight
                        ]));
                    }
                }
                
                // Complete the repair card
                $now = now();
                $repairCard->completed_at = $now;
                $repairCard->wireUsages()->update(['completed_at' => $now]);
                
                // Update wire transactions
                WireTransaction::where('repair_card_id', $repairCard->id)
                    ->update(['completed_at' => $now]);
                    
                $message = __('messages.repair_card_completed');
                
                \Log::info('Repair card completed successfully', ['repair_card_id' => $repairCard->id]);
            }

            $repairCard->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'completed' => !is_null($repairCard->completed_at)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error toggling repair card completion:', [
                'repair_card_id' => $repairCard->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    protected function findLastCompletedUsage($wireId, $currentRepairCardId = null)
    {
        $query = WireUsage::where('wire_inventory_id', $wireId)
            ->whereNotNull('completed_at');
        
        if ($currentRepairCardId) {
            $query->where('repair_card_id', '!=', $currentRepairCardId);
        }
        
        return $query->latest('completed_at')->first();
    }

    public function destroy(EngineRepairCard $repairCard)
    {
        DB::beginTransaction();
        try {
            // Delete associated records using relationships
            $repairCard->wireUsages()->forceDelete();
            $repairCard->originalWires()->forceDelete();
            $repairCard->scrapTransactions()->forceDelete();
            
            // Delete the repair card using Eloquent
            $repairCard->forceDelete();
            
            DB::commit();
            return redirect()->route('repair-cards.index')
                ->with('success', __('messages.repair_card_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete failed:', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', __('messages.error_deleting_repair_card') . ': ' . $e->getMessage());
        }
    }

    protected function validateRequest(Request $request, ?EngineRepairCard $repairCard = null): array
    {
        $rules = [
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
            'wires_in_groove' => 'nullable|integer|min:0',
            'wire' => 'nullable|string',
            'scrap_weight' => 'nullable|numeric|min:0',
            'total_wire_weight' => 'nullable|numeric|min:0',
            'winding_resistance' => 'nullable|string',
            'mass_resistance' => 'nullable|string',
            'notes' => 'nullable|string',
            'completed_at' => 'nullable|date',
        ];

        // Add unique validation for task_number and repair_card_number if creating new card
        if (!$repairCard) {
            $rules['task_number'] .= '|unique:engine_repair_cards';
            $rules['repair_card_number'] .= '|unique:engine_repair_cards';
        } else {
            $rules['task_number'] .= '|unique:engine_repair_cards,task_number,' . $repairCard->id;
            $rules['repair_card_number'] .= '|unique:engine_repair_cards,repair_card_number,' . $repairCard->id;
        }

        $messages = [
            'task_number.required' => __('validation.required', ['attribute' => __('messages.task_number')]),
            'repair_card_number.required' => __('validation.required', ['attribute' => __('messages.repair_card_number')]),
            'crown_height.numeric' => __('validation.numeric', ['attribute' => __('messages.crown_height')]),
            'wires_in_groove.min' => __('validation.min.numeric', ['attribute' => __('messages.wires_in_groove'), 'min' => 0]),
            'task_number.unique' => __('validation.unique', ['attribute' => __('messages.task_number')]),
            'repair_card_number.unique' => __('validation.unique', ['attribute' => __('messages.repair_card_number')]),
        ];

        $validated = $request->validate($rules, $messages);

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

