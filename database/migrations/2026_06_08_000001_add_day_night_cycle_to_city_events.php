<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->boolean('is_day_night_cycle')->default(false)->after('description');
            $table->unsignedInteger('day_duration_value')->nullable()->after('is_day_night_cycle');
            $table->string('day_duration_unit', 10)->nullable()->after('day_duration_value');
            $table->unsignedInteger('night_duration_value')->nullable()->after('day_duration_unit');
            $table->string('night_duration_unit', 10)->nullable()->after('night_duration_value');
            $table->string('current_phase', 5)->nullable()->after('night_duration_unit');
            $table->timestamp('phase_started_at')->nullable()->after('current_phase');
        });
    }

    public function down(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn([
                'is_day_night_cycle',
                'day_duration_value',
                'day_duration_unit',
                'night_duration_value',
                'night_duration_unit',
                'current_phase',
                'phase_started_at',
            ]);
        });
    }
};
