<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EngineRepairCardController;
use App\Http\Controllers\WireInventoryController;
use App\Http\Controllers\ScrapInventoryController;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('repair-cards.index');
});

// Language Switcher
Route::get('language/{locale}', [LanguageController::class, 'switchLang'])->name('language.switch');

// Authentication routes
Auth::routes();

// Engine Repair Cards routes
Route::middleware(['auth'])->group(function () {
    // Repair Cards
    Route::get('/repair-cards', [EngineRepairCardController::class, 'index'])->name('repair-cards.index');
    Route::get('/repair-cards/create', [EngineRepairCardController::class, 'create'])->name('repair-cards.create');
    Route::post('/repair-cards', [EngineRepairCardController::class, 'store'])->name('repair-cards.store');
    Route::get('/repair-cards/{repairCard}/edit', [EngineRepairCardController::class, 'edit'])->name('repair-cards.edit');
    Route::put('/repair-cards/{repairCard}', [EngineRepairCardController::class, 'update'])->name('repair-cards.update');
    Route::delete('/repair-cards/{repairCard}', [EngineRepairCardController::class, 'destroy'])->name('repair-cards.destroy');
    Route::post('/repair-cards/{repairCard}/toggle-complete', [EngineRepairCardController::class, 'toggleComplete'])
        ->name('repair-cards.toggle-complete');
    
    // Wire Inventory
    Route::get('wire-inventory', [WireInventoryController::class, 'index'])->name('wire-inventory.index');
    Route::post('wire-inventory', [WireInventoryController::class, 'store'])->name('wire-inventory.store');
    Route::put('wire-inventory/{wireInventory}', [WireInventoryController::class, 'update'])->name('wire-inventory.update');
    Route::post('wire-inventory/{wireInventory}/add-stock', [WireInventoryController::class, 'addStock'])->name('wire-inventory.add-stock');
    Route::post('wire-inventory/{wireInventory}/remove-stock', [WireInventoryController::class, 'removeStock'])->name('wire-inventory.remove-stock');
    Route::delete('wire-inventory/transactions/{transaction}', [WireInventoryController::class, 'deleteTransaction'])->name('wire-inventory.delete-transaction');

    // Scrap Inventory
    Route::get('/scrap', [ScrapInventoryController::class, 'index'])->name('scrap.index');
    Route::post('/scrap/add-initial', [ScrapInventoryController::class, 'addInitialBalance'])->name('scrap.add-initial');
    Route::post('/scrap/writeoff', [ScrapInventoryController::class, 'writeOff'])->name('scrap.writeoff');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
