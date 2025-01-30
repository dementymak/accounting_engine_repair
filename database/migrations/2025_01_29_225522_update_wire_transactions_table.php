<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('wire_transactions');
        
        Schema::create('wire_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wire_id')->constrained('wire_inventory')->onDelete('cascade');
            $table->foreignId('repair_card_id')->nullable()->constrained('engine_repair_cards')->onDelete('set null');
            $table->enum('type', ['income', 'expenditure']);
            $table->decimal('amount', 10, 2); // Positive for income, negative for expenditure
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wire_transactions');
    }
};
