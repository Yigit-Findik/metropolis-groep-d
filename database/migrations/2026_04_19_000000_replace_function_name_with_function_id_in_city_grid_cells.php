<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_grid_cells', function (Blueprint $table) {
            $table->dropColumn('function_name');
            $table->foreignId('function_id')
                ->nullable()
                ->constrained('city_functions')
                ->nullOnDelete()
                ->after('column_index');
        });
    }

    public function down(): void
    {
        Schema::table('city_grid_cells', function (Blueprint $table) {
            $table->dropForeign(['function_id']);
            $table->dropColumn('function_id');
            $table->string('function_name')->nullable()->after('column_index');
        });
    }
};
