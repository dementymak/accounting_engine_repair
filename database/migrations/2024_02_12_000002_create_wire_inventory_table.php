<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wire_inventory', function (Blueprint $table) {
            $table->id();
            $table->decimal('diameter', 10, 2);
            $table->decimal('weight', 10, 2);
            $table->timestamps();
            $table->unique('diameter');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wire_inventory');
    }
}; 