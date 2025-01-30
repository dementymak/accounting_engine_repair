<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('engine_repair_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique();
            $table->timestamp('completed_at')->nullable();
            $table->double('crown_height')->nullable();
            $table->enum('connection_type', ['serial', 'parallel'])->nullable();
            $table->string('connection_notes')->nullable();
            $table->string('groove_distances')->nullable(); // Stores U values as JSON
            $table->integer('wires_in_groove')->nullable();
            $table->string('wire')->nullable();
            $table->string('temperature_sensor')->nullable();
            $table->decimal('scrap_weight', 10, 2)->nullable();
            $table->decimal('total_wire_weight', 10, 2)->nullable();
            $table->string('winding_resistance')->nullable();
            $table->string('mass_resistance')->nullable();
            $table->string('model', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps(); // This creates both created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('engine_repair_cards');
    }
}; 