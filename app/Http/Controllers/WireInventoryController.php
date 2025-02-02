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
        $wires = WireInventory::with('transactions')->orderBy('diameter')->get();
        $wireTransactions = WireTransaction::with('wire')->orderBy('created_at', 'desc')->paginate(10);
        return view('wire-inventory.index', compact('wires', 'wireTransactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'diameter' => 'required|numeric|unique:wire_inventory',
            'weight' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($validated) {
            $wire = WireInventory::create([
                'diameter' => $validated['diameter'],
                'weight' => $validated['weight']
            ]);

            WireTransaction::create([
                'wire_id' => $wire->id,
                'amount' => $validated['weight'],
                'type' => 'income',
                'notes' => 'Initial balance'
            ]);
        });

        return redirect()->route('wire-inventory.index')
            ->with('success', __('messages.wire_added'));
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
        $validated = $request->validate([
            'additional_weight' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($wire, $validated) {
            $wire->weight += $validated['additional_weight'];
            $wire->save();

            WireTransaction::create([
                'wire_id' => $wire->id,
                'amount' => $validated['additional_weight'],
                'type' => 'income',
                'notes' => 'Stock addition'
            ]);
        });

        return redirect()->route('wire-inventory.index')
            ->with('success', __('messages.stock_added'));
    }

    public function removeStock(Request $request, WireInventory $wire)
    {
        $validated = $request->validate([
            'remove_weight' => 'required|numeric|min:0'
        ]);

        if ($validated['remove_weight'] > $wire->weight) {
            return back()->withErrors(['remove_weight' => __('messages.insufficient_stock')]);
        }

        DB::transaction(function () use ($wire, $validated) {
            $wire->weight -= $validated['remove_weight'];
            $wire->save();

            WireTransaction::create([
                'wire_id' => $wire->id,
                'amount' => -$validated['remove_weight'],
                'type' => 'expenditure',
                'notes' => 'Stock removal'
            ]);
        });

        return redirect()->route('wire-inventory.index')
            ->with('success', __('messages.stock_removed'));
    }

    public function deleteTransaction(WireTransaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $wire = $transaction->wire;
            
            // Reverse the transaction amount
            if ($transaction->type === 'expenditure') {
                $wire->weight += abs($transaction->amount);
            } else {
                $wire->weight -= $transaction->amount;
            }
            
            if ($wire->weight < 0) {
                throw new \Exception(__('messages.insufficient_stock'));
            }
            
            $wire->save();
            $transaction->delete();
        });

        return redirect()->route('wire-inventory.index')
            ->with('success', __('messages.transaction_deleted'));
    }
} 






