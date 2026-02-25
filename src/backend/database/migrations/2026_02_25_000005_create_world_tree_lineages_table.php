<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the evolutionary world tree tracking for the Civilization Meta-simulator.
     */
    public function up(): void
    {
        Schema::create('world_tree_lineages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Evolutionary Link
            $table->uuid('parent_id')->nullable()->index();
                  
            $table->unsignedBigInteger('generation')->default(0);
            
            // Core Identity
            $table->string('kernel_version', 20);
            $table->string('genre_preset', 50)->default('base'); // Empty Core / Xianxia / Post-Apoc
            
            // Final Evaluation
            $table->enum('status', ['active', 'extinct', 'ascended', 'collapsed', 'pruned'])->default('active');
            $table->string('collapse_reason')->nullable();
            
            // Survival Metrics
            $table->unsignedBigInteger('ticks_survived')->default(0);
            $table->unsignedInteger('singularity_spikes')->default(0);
            
            // Evolutionary Fitness Score (Evaluation of this world branch)
            $table->decimal('fitness_score', 12, 6)->nullable()->index();
            
            // Parameter & Genesis Snapshot Vectors 
            $table->json('genesis_vector'); 
            $table->json('mutation_deltas')->nullable(); // Explains what changed from parent
            
            // Agent Population
            $table->unsignedInteger('initial_agent_count');
            $table->unsignedInteger('final_agent_diversity_score')->nullable();
            
            // Immutability hashes
            $table->string('final_snapshot_hash', 64)->nullable();
            
            $table->timestamps();
        });

        Schema::table('world_tree_lineages', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('world_tree_lineages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_tree_lineages');
    }
};
