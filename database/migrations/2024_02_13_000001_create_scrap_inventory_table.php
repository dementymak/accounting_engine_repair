<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scrap_inventory', function (Blueprint $table) {
            $table->id();
            $table->decimal('weight', 10, 2);
            $table->timestamps();
        });

        Schema::create('scrap_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['initial', 'repair_card', 'writeoff']);
            $table->decimal('weight', 10, 2);
            $table->foreignId('repair_card_id')->nullable()->constrained('engine_repair_cards')->onDelete('set null');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scrap_transactions');
        Schema::dropIfExists('scrap_inventory');
    }
}; 