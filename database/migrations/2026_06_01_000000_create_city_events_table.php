<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('event_type');
            $table->unsignedInteger('recurring_frequency_value')->nullable();
            $table->string('recurring_frequency_unit')->nullable();
            $table->unsignedInteger('one_off_duration_value')->nullable();
            $table->string('one_off_duration_unit')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_events');
    }
};