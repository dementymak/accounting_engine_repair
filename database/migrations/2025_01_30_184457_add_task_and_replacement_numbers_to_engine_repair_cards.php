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
        Schema::table('engine_repair_cards', function (Blueprint $table) {
            $table->string('task_number')->nullable()->after('number');
            $table->string('repair_card_number')->nullable()->after('task_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engine_repair_cards', function (Blueprint $table) {
            $table->dropColumn(['task_number', 'repair_card_number']);
        });
    }
};
