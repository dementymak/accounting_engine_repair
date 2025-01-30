<?php

namespace App\Http\Controllers;

use App\Models\WireInventory;
use App\Models\WireTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WireInventoryController extends Controller
{
    public function index()
    {
        $wires = WireInventory::orderBy('diameter')->get();
        $wireTransactions = WireTransaction::with(['wire', 'repair_card'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('wire-inventory.index', compact('wires', 'wireTransactions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'diameter' => 'required|numeric|unique:wire_inventory',
            'weight' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wire = WireInventory::create($request->all());

        // Record initial stock transaction
        WireTransaction::create([
            'wire_id' => $wire->id,
            'type' => 'income',
            'amount' => $request->weight,
            'notes' => 'Initial stock',
        ]);

        return redirect()->route('wire-inventory.index')
            ->with('success', 'Wire added successfully.');
    }

    public function update(Request $request, WireInventory $wireInventory)
    {
        $validator = Validator::make($request->all(), [
            'diameter' => 'required|numeric|unique:wire_inventory,diameter,' . $wireInventory->id,
            'weight' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wireInventory->update($request->all());

        return redirect()->route('wire-inventory.index')
            ->with('success', 'Wire updated successfully.');
    }

    public function addStock(Request $request, WireInventory $wire)
    {
        $validator = Validator::make($request->all(), [
            'additional_weight' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wire->weight += $request->additional_weight;
        $wire->save();

        // Record stock addition transaction
        WireTransaction::create([
            'wire_id' => $wire->id,
            'type' => 'income',
            'amount' => $request->additional_weight,
        ]);

        return redirect()->route('wire-inventory.index')
            ->with('success', 'Stock added successfully.');
    }

    public function removeStock(Request $request, WireInventory $wire)
    {
        $validator = Validator::make($request->all(), [
            'remove_weight' => "required|numeric|min:0|max:{$wire->weight}",
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $wire->weight -= $request->remove_weight;
        $wire->save();

        // Record stock removal transaction
        WireTransaction::create([
            'wire_id' => $wire->id,
            'type' => 'expenditure',
            'amount' => -$request->remove_weight,
        ]);

        return redirect()->route('wire-inventory.index')
            ->with('success', 'Stock removed successfully.');
    }
} 


