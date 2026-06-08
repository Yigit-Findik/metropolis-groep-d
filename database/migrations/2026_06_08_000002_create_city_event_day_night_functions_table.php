<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_event_day_night_functions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_function_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 5); // 'day' or 'night'
            $table->integer('safety_modifier')->default(0);
            $table->integer('recreation_modifier')->default(0);
            $table->integer('environment_quality_modifier')->default(0);
            $table->integer('facilities_modifier')->default(0);
            $table->integer('mobility_modifier')->default(0);
            $table->timestamps();
            $table->unique(['city_event_id', 'city_function_id', 'phase'], 'dnc_fn_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_event_day_night_functions');
    }
};
