<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('original_wires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_card_id')->constrained('engine_repair_cards')->onDelete('cascade');
            $table->decimal('diameter', 10, 2);
            $table->integer('wire_count');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('original_wires');
    }
}; 