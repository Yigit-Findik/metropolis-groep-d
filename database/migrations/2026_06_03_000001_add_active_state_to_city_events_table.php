<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('one_off_duration_unit');
            $table->timestamp('activated_at')->nullable()->after('is_active');
            $table->timestamp('expires_at')->nullable()->after('activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('city_events', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'activated_at', 'expires_at']);
        });
    }
};
