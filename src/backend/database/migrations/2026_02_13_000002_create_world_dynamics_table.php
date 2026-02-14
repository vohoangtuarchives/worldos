<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_dynamics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->onDelete('cascade');
            $table->integer('tick')->default(0);
            $table->float('entropy_level')->default(0.0);
            $table->float('instability_score')->default(0.0);
            $table->float('civilization_phase')->default(1.0);
            $table->integer('active_shocks')->default(0);
            $table->json('power_distribution')->nullable(); // Faction power levels
            $table->json('resource_levels')->nullable(); // Resource availability
            $table->json('myth_stability')->nullable(); // Myth system stability
            $table->timestamp('last_shock_event')->nullable();
            $table->timestamp('next_tick_scheduled')->nullable();
            $table->boolean('is_autonomous')->default(false);
            $table->json('autonomous_config')->nullable(); // Config for autonomous behavior
            $table->timestamps();

            $table->unique(['world_id', 'tick']);
            $table->index('entropy_level');
            $table->index('instability_score');
            $table->index(['is_autonomous', 'next_tick_scheduled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_dynamics');
    }
};
