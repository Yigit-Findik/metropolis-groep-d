<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('city_events')->where('is_day_night_cycle', true)->exists()) {
            return;
        }

        DB::table('city_events')->insert([
            'name'                 => 'Day/Night Cycle',
            'description'          => 'Simulates the natural day and night cycle, applying different effects to city functions during each phase.',
            'event_type'           => 'day-night',
            'is_day_night_cycle'   => true,
            'is_active'            => false,
            'day_duration_value'   => 12,
            'day_duration_unit'    => 'hour',
            'night_duration_value' => 12,
            'night_duration_unit'  => 'hour',
            'current_phase'        => null,
            'phase_started_at'     => null,
            'activated_at'         => null,
            'expires_at'           => null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('city_events')->where('is_day_night_cycle', true)->delete();
    }
};
