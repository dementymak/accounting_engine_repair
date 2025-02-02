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
        Schema::table('wire_usages', function (Blueprint $table) {
            $table->foreignId('previous_repair_card_id')->nullable()
                ->after('repair_card_id')
                ->constrained('engine_repair_cards')
                ->onDelete('set null');
            
            $table->index('previous_repair_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wire_usages', function (Blueprint $table) {
            $table->dropForeign(['previous_repair_card_id']);
            $table->dropColumn('previous_repair_card_id');
        });
    }
};
