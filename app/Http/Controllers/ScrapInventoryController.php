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
        $transactions = ScrapTransaction::orderBy('created_at', 'desc')->get();
        return view('scrap.index', compact('scrapInventory', 'transactions'));
    }

    public function addInitial(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            $scrap = ScrapInventory::firstOrCreate(
                [],
                ['weight' => 0]
            );

            $scrap->weight += $validated['weight'];
            $scrap->save();

            ScrapTransaction::create([
                'type' => 'initial',
                'weight' => $validated['weight'],
                'notes' => $validated['notes'] ?? null
            ]);
        });

        return redirect()->route('scrap.index');
    }

    public function writeoff(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $scrap = ScrapInventory::firstOrCreate(
            [],
            ['weight' => 0]
        );

        if ($scrap->weight < $validated['weight']) {
            return back()->withErrors(['weight' => 'Insufficient scrap available.']);
        }

        DB::transaction(function () use ($scrap, $validated) {
            $scrap->weight -= $validated['weight'];
            $scrap->save();

            ScrapTransaction::create([
                'type' => 'writeoff',
                'weight' => -$validated['weight'],
                'notes' => $validated['notes'] ?? null
            ]);
        });

        return redirect()->route('scrap.index');
    }
} 