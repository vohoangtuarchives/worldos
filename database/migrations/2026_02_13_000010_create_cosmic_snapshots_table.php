<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cosmic_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->integer('year');

            // Cosmic State Vector (7D)
            $table->float('energy')->default(0.5);
            $table->float('entropy')->default(0.3);
            $table->float('tension')->default(0.1);
            $table->float('stability')->default(0.6);
            $table->float('resonance')->default(0.0);
            $table->float('information_density')->default(0.1);
            $table->float('transcendence')->default(0.0);

            // Attractor
            $table->string('attractor', 64)->default('EQUILIBRIUM');

            // Environment (snapshot)
            $table->float('env_ley_energy')->default(0.5);
            $table->float('env_terrain_stability')->default(0.7);
            $table->float('env_biosphere_vitality')->default(0.6);
            $table->float('env_anomaly_density')->default(0.1);

            // Civilization (snapshot)
            $table->float('civ_knowledge')->default(0.1);
            $table->float('civ_ritual_coherence')->default(0.2);
            $table->float('civ_tech_level')->default(0.05);
            $table->float('civ_faction_stability')->default(0.5);
            $table->float('civ_resonance_accumulator')->default(0.0);

            // Composite metrics
            $table->float('composite_tension')->default(0.0);

            $table->timestamps();

            // Indexes
            $table->unique(['world_id', 'year']);
            $table->index(['attractor']);
            $table->index(['world_id', 'attractor']);
        });

        Schema::create('cosmic_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->integer('year');
            $table->string('type', 32); // MINOR_BIFURCATION, MAJOR_BIFURCATION
            $table->string('from_attractor', 64);
            $table->string('to_attractor', 64);
            $table->float('force');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['world_id', 'year']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmic_events');
        Schema::dropIfExists('cosmic_snapshots');
    }
};
