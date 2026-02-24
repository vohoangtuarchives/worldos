<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evolution_experiments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('Cosmic Evolution Run');
            $table->string('status', 32)->default('running');
            $table->timestamps();
        });

        Schema::create('evolution_generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('experiment_id');
            $table->integer('generation_index');
            $table->integer('population_size');
            $table->string('status', 32)->default('running');
            $table->timestamps();

            $table->foreign('experiment_id')->references('id')->on('evolution_experiments')->onDelete('cascade');
        });

        Schema::table('universes', function (Blueprint $table) {
            $table->uuid('generation_id')->nullable()->after('world_blueprint_id');
            
            $table->integer('lifespan')->default(0)->after('status');
            $table->float('fitness_total_score')->default(0.0)->after('lifespan');
            $table->float('fitness_stability_score')->default(0.0)->after('fitness_total_score');
            $table->float('fitness_complexity_score')->default(0.0)->after('fitness_stability_score');
            $table->json('seed_dna')->nullable()->after('state_vector');

            $table->foreign('generation_id')->references('id')->on('evolution_generations')->onDelete('set null');
        });

        Schema::create('cosmological_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generation_id');
            $table->float('entropy_background')->default(0.0);
            $table->float('turbulence_pressure')->default(0.0);
            $table->json('mythic_resonance_json')->nullable();
            $table->float('spectral_drift')->default(0.0);
            $table->timestamps();

            $table->foreign('generation_id')->references('id')->on('evolution_generations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cosmological_fields');
        
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['generation_id']);
            $table->dropColumn([
                'generation_id',
                'lifespan',
                'fitness_total_score',
                'fitness_stability_score',
                'fitness_complexity_score',
                'seed_dna'
            ]);
        });
        
        Schema::dropIfExists('evolution_generations');
        Schema::dropIfExists('evolution_experiments');
    }
};
