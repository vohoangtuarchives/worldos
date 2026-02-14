<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('world_power_stages', function (Blueprint $table) {
            $table->string('transition_phase')->default('stable'); // stable, pre, moment, post
            $table->string('target_stage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_power_stages', function (Blueprint $table) {
            $table->dropColumn(['transition_phase', 'target_stage']);
        });
    }
};
