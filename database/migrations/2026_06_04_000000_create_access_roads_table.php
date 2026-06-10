<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_roads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('start_cell_id')->constrained('city_grid_cells')->onDelete('cascade');
            $table->foreignId('end_cell_id')->constrained('city_grid_cells')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('access_road_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_road_id')->constrained()->onDelete('cascade');
            $table->foreignId('cell_id')->constrained('city_grid_cells')->onDelete('cascade');
            $table->unsignedInteger('cell_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_road_cells');
        Schema::dropIfExists('access_roads');
    }
};