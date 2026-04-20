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
        Schema::create('city_grid_cells', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('row_index');
            $table->unsignedTinyInteger('column_index');
            $table->string('function_name')->nullable();
            $table->timestamps();

            $table->unique(['row_index', 'column_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_grid_cells');
    }
};
