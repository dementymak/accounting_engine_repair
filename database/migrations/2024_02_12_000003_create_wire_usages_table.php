<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wire_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_card_id')->constrained('engine_repair_cards')->onDelete('cascade');
            $table->foreignId('wire_inventory_id')->constrained('wire_inventory');
            $table->decimal('initial_weight', 10, 2);
            $table->decimal('used_weight', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wire_usages');
    }
}; 
