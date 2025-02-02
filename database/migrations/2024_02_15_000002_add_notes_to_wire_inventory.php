<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('wire_inventory', function (Blueprint $table) {
            $table->string('notes')->nullable()->after('weight');
        });
    }

    public function down()
    {
        Schema::table('wire_inventory', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}; 