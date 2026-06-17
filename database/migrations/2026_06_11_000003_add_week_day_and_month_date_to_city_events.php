<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->tinyInteger('recurring_week_day')->nullable()->after('recurring_time_slots');
            $table->tinyInteger('recurring_month_date')->nullable()->after('recurring_week_day');
        });
    }

    public function down(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn(['recurring_week_day', 'recurring_month_date']);
        });
    }
};
