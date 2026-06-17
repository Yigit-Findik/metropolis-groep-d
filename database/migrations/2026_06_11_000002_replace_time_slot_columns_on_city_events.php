<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn(['recurring_time_slot_start', 'recurring_time_slot_end']);
            $table->json('recurring_time_slots')->nullable()->after('recurring_active_duration_unit');
        });
    }

    public function down(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn('recurring_time_slots');
            $table->string('recurring_time_slot_start', 5)->nullable()->after('recurring_active_duration_unit');
            $table->string('recurring_time_slot_end', 5)->nullable()->after('recurring_time_slot_start');
        });
    }
};
