<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_function_id')->nullable()->constrained('city_functions')->nullOnDelete();
            $table->string('function_name');
            $table->string('trigger_type');
            $table->string('status')->default('pending');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'trigger_type']);
            $table->index(['city_function_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_actions');
    }
};