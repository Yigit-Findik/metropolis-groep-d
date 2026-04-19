<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_functions', function (Blueprint $table) {
            $table->integer('livability')->default(0)->after('qol_score');
            $table->integer('safety')->default(0)->after('livability');
            $table->integer('economy')->default(0)->after('safety');
            $table->integer('environment')->default(0)->after('economy');
            $table->integer('welfare')->default(0)->after('environment');
        });
    }

    public function down(): void
    {
        Schema::table('city_functions', function (Blueprint $table) {
            $table->dropColumn(['livability', 'safety', 'economy', 'environment', 'welfare']);
        });
    }
};
