<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WorldOS V5: Full rewrite of the universes table.
 * Clean schema aligned with V5 Simulation Domain entities.
 * Drops old V3 table completely and rebuilds.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only run raw CASCADE on PostgreSQL. SQLite does not support it.
        if (config('database.default') === 'pgsql') {
            \DB::statement('DROP TABLE IF EXISTS universe_snapshots CASCADE');
            \DB::statement('DROP TABLE IF EXISTS universes CASCADE');
        } else {
            Schema::dropIfExists('universe_snapshots');
            Schema::dropIfExists('universes');
        }

        Schema::create('universes', function (Blueprint $table) {
            // --- Identity ---
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status', 32)->default('pending'); // pending, running, paused, collapsed, archived

            // --- Lineage (DAG) ---
            $table->string('world_blueprint_id');    // Links to V5 World Blueprint (Blueprint Context)
            $table->string('multiverse_id');         // Groups all universes in same DAG multiverse
            $table->uuid('parent_universe_id')->nullable(); // Null = root universe; non-null = forked child

            // --- Simulation State ---
            $table->unsignedBigInteger('current_tick')->default(0);
            $table->unsignedBigInteger('current_seed')->default(0);  // Last seed used (for replay)
            $table->float('entropy')->default(0.0);
            $table->float('stability_index')->default(1.0);
            $table->float('existence_weight')->default(1.0);         // CompiledPolicy weight formula output
            $table->json('state_vector');                             // Full 17D StateVector JSON

            $table->timestamps();
            $table->softDeletes();

            // --- Indexes ---
            $table->index('multiverse_id');
            $table->index('world_blueprint_id');
            $table->index('parent_universe_id');
            $table->index(['multiverse_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universes');
    }
};
