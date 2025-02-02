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
        Schema::table('wire_transactions', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('notes');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wire_transactions', function (Blueprint $table) {
            $table->dropIndex(['completed_at']);
            $table->dropColumn('completed_at');
        });
    }
};
