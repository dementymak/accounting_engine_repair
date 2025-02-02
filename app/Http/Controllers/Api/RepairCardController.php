<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EngineRepairCard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RepairCardController extends Controller
{
    public function index()
    {
        $repairCards = EngineRepairCard::all();
        return response()->json(['data' => $repairCards]);
    }

    public function show(EngineRepairCard $repairCard)
    {
        return response()->json(['data' => $repairCard]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_number' => 'required|unique:engine_repair_cards',
            'repair_card_number' => 'required|unique:engine_repair_cards',
            'model' => 'nullable|string',
            'groove_distances' => 'nullable|string'
        ]);

        if (!empty($validated['groove_distances'])) {
            $validated['groove_distances'] = explode('/', $validated['groove_distances']);
        }

        $repairCard = EngineRepairCard::create($validated);

        return response()->json(['data' => $repairCard], Response::HTTP_CREATED);
    }

    public function update(Request $request, EngineRepairCard $repairCard)
    {
        $validated = $request->validate([
            'task_number' => 'required|unique:engine_repair_cards,task_number,' . $repairCard->id,
            'repair_card_number' => 'required|unique:engine_repair_cards,repair_card_number,' . $repairCard->id,
            'model' => 'nullable|string',
            'groove_distances' => 'nullable|string'
        ]);

        if (!empty($validated['groove_distances'])) {
            $validated['groove_distances'] = explode('/', $validated['groove_distances']);
        }

        $repairCard->update($validated);

        return response()->json(['data' => $repairCard]);
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
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete failed:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 