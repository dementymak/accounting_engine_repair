<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wire_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wire_inventory_id')->constrained('wire_inventory')->onDelete('cascade');
            $table->foreignId('repair_card_id')->constrained('engine_repair_cards')->onDelete('cascade');
            $table->decimal('reserved_weight', 10, 2);
            $table->decimal('initial_stock_weight', 10, 2)->comment('Stock weight at the time of reservation');
            $table->timestamps();

            $table->index(['wire_inventory_id', 'repair_card_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wire_reservations');
    }
}; 