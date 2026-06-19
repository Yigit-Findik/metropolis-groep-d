<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mark any recurring event that has been activated before as in-simulation
        DB::table('city_events')
            ->where('event_type', 'recurring')
            ->whereNotNull('activated_at')
            ->whereNull('deleted_at')
            ->update(['is_in_simulation' => true]);
    }

    public function down(): void
    {
        //
    }
};
