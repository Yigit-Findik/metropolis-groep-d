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
        Schema::create('function_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_function_id')->constrained('city_functions')->onDelete('cascade');
            $table->foreignId('target_function_id')->constrained('city_functions')->onDelete('cascade');
            $table->enum('type', ['required', 'forbidden']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('function_conditions');
    }
};
