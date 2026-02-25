<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kernel_experiment_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('experiment_id');
            $table->unsignedInteger('tick');
            
            // System state (Single Source of Truth)
            $table->json('state_vector');
            $table->json('input_vector')->nullable(); // External forces applied
            
            // Structural parameters tracking (to guarantee immutability/reproducibility)
            $table->json('structure_params'); // contains A, L, alpha, lambda, eta at this tick
            $table->json('rng_state')->nullable(); // The seeded PRNG state

            // Hash chain - MUST NOT BE MODIFIED
            $table->string('snapshot_hash', 64);
            $table->string('previous_hash', 64);

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('experiment_id')
                  ->references('id')
                  ->on('kernel_experiments')
                  ->onDelete('cascade');

            $table->unique(['experiment_id', 'tick']);
            $table->unique('snapshot_hash');
            $table->index(['experiment_id', 'previous_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kernel_experiment_snapshots');
    }
};
