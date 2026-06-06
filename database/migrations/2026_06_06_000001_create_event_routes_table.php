<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('access_road_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_cell_id')->constrained('city_grid_cells')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('event_route_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cell_id')->constrained('city_grid_cells')->cascadeOnDelete();
            $table->integer('cell_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_route_cells');
        Schema::dropIfExists('event_routes');
    }
};
