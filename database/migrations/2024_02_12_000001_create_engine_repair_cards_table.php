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
            $table->string('task_number');
            $table->string('repair_card_number');
            $table->string('model', 100)->nullable();
            $table->string('temperature_sensor')->nullable();
            $table->double('crown_height')->nullable();
            $table->enum('connection_type', ['serial', 'parallel'])->nullable();
            $table->string('connection_notes')->nullable();
            $table->json('groove_distances')->nullable(); // Stores distances as JSON array
            $table->integer('wires_in_groove')->nullable();
            $table->decimal('scrap_weight', 10, 2)->nullable();
            $table->decimal('total_wire_weight', 10, 2)->nullable();
            $table->string('winding_resistance')->nullable();
            $table->string('mass_resistance')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('task_number');
            $table->index('repair_card_number');
            $table->index('completed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('engine_repair_cards');
    }
}; 