<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EngineRepairCardController;
use App\Http\Controllers\WireInventoryController;
use App\Http\Controllers\ScrapInventoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AuthController;

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

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Language switching
Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Protected routes
Route::middleware('auth')->group(function () {
    // Redirect root to repair cards
    Route::get('/', function () {
        return redirect()->route('repair-cards.index');
    });

    // Repair Cards
    Route::get('repair-cards', [EngineRepairCardController::class, 'index'])->name('repair-cards.index');
    Route::get('repair-cards/create', [EngineRepairCardController::class, 'create'])->name('repair-cards.create');
    Route::post('repair-cards', [EngineRepairCardController::class, 'store'])->name('repair-cards.store');
    Route::get('repair-cards/{repairCard}/edit', [EngineRepairCardController::class, 'edit'])->name('repair-cards.edit');
    Route::put('repair-cards/{repairCard}', [EngineRepairCardController::class, 'update'])->name('repair-cards.update');
    Route::delete('repair-cards/{repairCard}', [EngineRepairCardController::class, 'destroy'])->name('repair-cards.destroy');
    Route::post('repair-cards/{repairCard}/toggle-complete', [EngineRepairCardController::class, 'toggleComplete'])->name('repair-cards.toggle-complete');

    // Wire Inventory
    Route::get('wire-inventory', [WireInventoryController::class, 'index'])->name('wire-inventory.index');
    Route::post('wire-inventory', [WireInventoryController::class, 'store'])->name('wire-inventory.store');
    Route::post('wire-inventory/{wire}/add-stock', [WireInventoryController::class, 'addStock'])->name('wire-inventory.add-stock');
    Route::post('wire-inventory/{wire}/remove-stock', [WireInventoryController::class, 'removeStock'])->name('wire-inventory.remove-stock');
    Route::delete('wire-inventory/transactions/{transaction}', [WireInventoryController::class, 'deleteTransaction'])->name('wire-inventory.delete-transaction');

    // Scrap Inventory
    Route::get('scrap', [ScrapInventoryController::class, 'index'])->name('scrap.index');
    Route::post('scrap/initial', [ScrapInventoryController::class, 'addInitial'])->name('scrap.add-initial');
    Route::post('scrap/writeoff', [ScrapInventoryController::class, 'writeoff'])->name('scrap.writeoff');
    Route::delete('scrap/transactions/{transaction}', [ScrapInventoryController::class, 'deleteTransaction'])->name('scrap.delete-transaction');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
