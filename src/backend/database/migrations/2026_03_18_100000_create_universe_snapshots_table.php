<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WorldOS v3: Snapshot-first. Each tick (or every N) we persist universe state
     * for rollback, fork, clone, and AI metrics. Index (universe_id, tick) for fast lookup.
     */
    public function up(): void
    {
        Schema::create('universe_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('universe_id');
            $table->unsignedBigInteger('tick')->default(0);
            $table->json('state_vector');
            $table->float('entropy')->nullable();
            $table->float('stability_index')->nullable();
            $table->json('metrics')->nullable(); // complexity_index, narrative_score, etc.
            $table->timestamps();

            $table->foreign('universe_id')->references('id')->on('universes')->onDelete('cascade');
            $table->index(['universe_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_snapshots');
    }
};
