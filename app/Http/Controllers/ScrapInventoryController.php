<?php

namespace App\Http\Controllers;

use App\Models\ScrapInventory;
use App\Models\ScrapTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScrapInventoryController extends Controller
{
    public function index()
    {
        $scrapInventory = ScrapInventory::first();
        $transactions = ScrapTransaction::with('repairCard')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('scrap.index', compact('scrapInventory', 'transactions'));
    }

    public function addInitialBalance(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $scrapInventory = ScrapInventory::firstOrCreate(
                [],
                ['weight' => 0]
            );

            ScrapTransaction::create([
                'type' => 'initial',
                'weight' => $request->weight,
                'notes' => $request->notes,
            ]);

            $scrapInventory->increment('weight', $request->weight);
            
            DB::commit();
            return redirect()->route('scrap.index')
                ->with('success', 'Initial balance added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding initial scrap balance: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error adding initial balance: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function writeOff(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $scrapInventory = ScrapInventory::firstOrFail();

            if ($scrapInventory->weight < $request->weight) {
                throw new \Exception('Insufficient scrap weight available for write-off.');
            }

            ScrapTransaction::create([
                'type' => 'writeoff',
                'weight' => -$request->weight,
                'notes' => $request->notes,
            ]);

            $scrapInventory->decrement('weight', $request->weight);
            
            DB::commit();
            return redirect()->route('scrap.index')
                ->with('success', 'Scrap written off successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error writing off scrap: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error writing off scrap: ' . $e->getMessage())
                ->withInput();
        }
    }
} 