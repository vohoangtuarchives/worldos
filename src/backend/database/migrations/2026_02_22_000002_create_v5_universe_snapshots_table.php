<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WorldOS V5: Universe Snapshots — deterministic tick captures for replay and AI analysis.
 * Each row represents a complete universe state at a given tick.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('universe_snapshots');

        Schema::create('universe_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');

            // --- Simulation Identity ---
            $table->unsignedBigInteger('tick');
            $table->unsignedBigInteger('seed');          // Seed used at this tick (enables deterministic replay)
            $table->float('entropy');
            $table->float('stability_index');
            $table->float('existence_weight');            // Weight computed by CompiledPolicy at this tick

            // --- State ---
            $table->json('state_vector');                 // Full 17D snapshot

            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            // --- Indexes ---
            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->onDelete('cascade');

            $table->index(['universe_id', 'tick']);       // Fast replay look-up
            $table->unique(['universe_id', 'tick']);      // One snapshot per tick per universe
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_snapshots');
    }
};
