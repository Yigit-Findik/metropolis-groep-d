<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * The table has to have this attributes: 
     * 1. id PK
     * 2. user_id FK
     * 3. action string
     * 4. cell_id FK
     * 5. old_city_function_id NULL
     * 6. new_city_function_id NULL
     * 7. timestamps DATETIME
     * 
     */
    public function up(): void
    {
        Schema::create('action_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->foreignId('cell_id')->constrained('city_grid_cells')->onDelete('cascade');
            $table->unsignedBigInteger('old_city_function_id')->nullable();
            $table->unsignedBigInteger('new_city_function_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_history');
    }
};
