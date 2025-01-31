<?php

namespace App\Http\Controllers;

use App\Models\WireInventory;
use App\Models\WireTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    public function addStock(Request $request, WireInventory $wireInventory)
    {
        $validator = Validator::make($request->all(), [
            'additional_weight' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update the wire weight using update to avoid creating a new record
            $wireInventory->update([
                'weight' => $wireInventory->weight + $request->additional_weight
            ]);

            // Record stock addition transaction
            WireTransaction::create([
                'wire_id' => $wireInventory->id,
                'type' => 'income',
                'amount' => $request->additional_weight,
            ]);

            DB::commit();
            return redirect()->route('wire-inventory.index')
                ->with('success', 'Stock added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error adding stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function removeStock(Request $request, WireInventory $wire)
    {
        try {
            DB::beginTransaction();

            // Get the current weight before validation
            $currentWeight = $wire->weight ?? 0;

            $validator = Validator::make($request->all(), [
                'remove_weight' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($currentWeight) {
                        if ($value > $currentWeight) {
                            $fail(__('messages.insufficient_stock', ['available' => $currentWeight]));
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update wire weight
            $wire->update([
                'weight' => $currentWeight - $request->remove_weight
            ]);

            // Record stock removal transaction
            WireTransaction::create([
                'wire_id' => $wire->id,
                'type' => 'expenditure',
                'amount' => -$request->remove_weight,
            ]);

            DB::commit();
            return redirect()->route('wire-inventory.index')
                ->with('success', 'Stock removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error removing stock: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteTransaction(WireTransaction $transaction)
    {
        try {
            DB::beginTransaction();

            // Get the wire inventory
            $wire = $transaction->wire;

            // Reverse the transaction amount in the wire inventory
            if ($transaction->type === 'income') {
                $wire->update([
                    'weight' => $wire->weight - $transaction->amount
                ]);
            } else { // expenditure
                $wire->update([
                    'weight' => $wire->weight + abs($transaction->amount)
                ]);
            }

            // Delete the transaction
            $transaction->delete();

            DB::commit();
            return redirect()->route('wire-inventory.index')
                ->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }
} 





