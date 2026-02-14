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
        // 1. Transactional Outbox
        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('aggregate_type'); // e.g., 'world', 'meta'
            $table->string('aggregate_id');
            $table->string('event_type');
            $table->jsonb('payload');
            $table->string('status')->default('pending'); // pending, processing, sent, failed
            $table->integer('retry_count')->default(0);
            $table->text('error_log')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['aggregate_type', 'aggregate_id']);
        });

        // 2. Meta Layer State (Singleton-like)
        Schema::create('meta_layer_states', function (Blueprint $table) {
            $table->id();
            // Core Physics
            $table->float('chaos_pool')->default(0);
            $table->float('entropy_pressure')->default(0);
            $table->float('resource_flux')->default(0.5);
            
            // Ideology Vector (JSONB)
            // { order: 0.5, chaos: 0.5, expansion: 0.5, consolidation: 0.5, diversity: 0.5 }
            $table->jsonb('ideology_vector');
            
            // Myth Field (JSONB)
            // { collapse_memory: 0.2, golden_age_bias: 0.1 }
            $table->jsonb('myth_field')->nullable();
            
            // Dynamics
            $table->float('aggression_index')->default(0);
            $table->float('stability_index')->default(0.5);
            $table->float('mutation_bias')->default(0.01);
            
            // Era Tracking
            $table->integer('current_era_index')->default(0);
            $table->timestamp('last_evolved_at')->nullable();
            
            $table->timestamps();
        });

        // 3. Meta Eras History
        Schema::create('meta_eras', function (Blueprint $table) {
            $table->id();
            $table->integer('era_index');
            $table->string('name')->nullable(); // e.g., "The Age of Fragmentation"
            $table->string('phase')->default('rise'); // rise, peak, decline, rebirth
            $table->integer('started_at_tick');
            $table->integer('ended_at_tick')->nullable();
            $table->jsonb('personality_vector'); // Snapshot of ideology at start
            $table->jsonb('context_data')->nullable(); // Why did this era start?
            $table->timestamps();
            
            $table->unique('era_index');
        });

        // 4. Meta Impulses (Meta -> World Communication)
        Schema::create('meta_impulses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // e.g., 'GlobalCrisis', 'IdeologyShift'
            $table->jsonb('payload');
            $table->float('strength'); // 0.0 - 1.0
            $table->float('decay_rate')->default(0.1);
            $table->integer('created_at_tick');
            $table->integer('active_until_tick')->nullable();
            $table->timestamps();
        });

        // 5. World Impulse Inbox (Pull model)
        Schema::create('world_impulse_inbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->onDelete('cascade');
            $table->uuid('impulse_id');
            $table->boolean('applied')->default(false);
            $table->integer('applied_at_tick')->nullable();
            $table->timestamps();
            
            $table->foreign('impulse_id')->references('id')->on('meta_impulses')->onDelete('cascade');
            $table->unique(['world_id', 'impulse_id']);
        });

        // 6. Sacred Archetypes (Evolved/Canonized)
        Schema::create('sacred_archetypes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Link to original definition
            $table->string('parent_archetype_key')->nullable(); 
            
            $table->string('name');
            $table->float('sacred_strength')->default(0.5); // 0.0 - 1.0
            $table->integer('canonized_at_tick');
            $table->integer('survival_eras')->default(0);
            
            // Mythic Resonance
            $table->jsonb('myth_profile'); // { heroism: 0.8, sacrifice: 0.2 }
            $table->jsonb('mutation_profile'); // Benefits for descendants
            
            // Status
            $table->string('status')->default('active'); // active, fading, forgotten
            $table->timestamps();
            
            $table->index('status');
        });

        // 7. World Snapshots V2 (Hybrid Storage for Replay)
        Schema::create('world_snapshots_v2', function (Blueprint $table) {
            $table->id();
            $table->uuid('simulation_run_id')->index(); // Link to a playback session
            $table->foreignId('world_id')->index();
            $table->integer('tick');
            
            // Structured Columns (Queryable)
            $table->integer('generation');
            $table->string('archetype_id')->nullable();
            $table->string('status'); // alive, collapsed
            $table->float('entropy');
            $table->float('survival_score');
            $table->boolean('is_prophet')->default(false);
            
            // Payload (State Reconstruction)
            $table->jsonb('state_payload');
            // { ideology_vector, resource_balance, mutation_profile, sacred_affinity }
            
            // Determinism
            $table->string('state_hash');
            
            $table->timestamps();
            
            $table->index(['simulation_run_id', 'tick']);
        });

        // 8. Meta Snapshots (For Replay)
        Schema::create('meta_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('simulation_run_id')->index();
            $table->integer('tick');
            
            // Structured
            $table->integer('current_era_index');
            $table->float('extinction_threshold');
            $table->float('drift_velocity');
            
            // Payload
            $table->jsonb('ideology_vector');
            $table->jsonb('sacred_state');
            
            // Determinism
            $table->string('meta_hash');
            
            $table->timestamps();
            
            $table->index(['simulation_run_id', 'tick']);
        });
        
        // 9. Simulation Runs (Replay Session Header)
        Schema::create('simulation_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('seed'); // Deterministic seed
            $table->string('config_hash');
            $table->string('mode')->default('normal'); // normal, replay
            $table->integer('current_tick')->default(0);
            $table->string('status')->default('running'); // running, completed, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_runs');
        Schema::dropIfExists('meta_snapshots');
        Schema::dropIfExists('world_snapshots_v2');
        Schema::dropIfExists('sacred_archetypes');
        Schema::dropIfExists('world_impulse_inbox');
        Schema::dropIfExists('meta_impulses');
        Schema::dropIfExists('meta_eras');
        Schema::dropIfExists('meta_layer_states');
        Schema::dropIfExists('outbox_messages');
    }
};
