<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->unsignedInteger('recurring_active_duration_value')->nullable()->after('recurring_frequency_unit');
            $table->string('recurring_active_duration_unit')->nullable()->after('recurring_active_duration_value');
        });
    }

    public function down(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn(['recurring_active_duration_value', 'recurring_active_duration_unit']);
        });
    }
};
