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
        // 1. Evolution Profiles (The Genome)
        Schema::create('evolution_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Standard Cultivation", "Cyberpunk Dystopia"
            $table->json('coefficients'); // { "belief_growth": 0.5, "entropy_decay": 0.1 }
            $table->float('alpha')->default(1.0); // Non-linear growth factor
            $table->json('thresholds'); // { "stability_critical": 0.3 }
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Enhance World States (The State Vector)
        Schema::table('world_states', function (Blueprint $table) {
            // Linking to the World (Missing in original migration)
            $table->foreignUuid('world_id')->nullable()->constrained('worlds')->cascadeOnDelete();

            // Adding the core mathematical vector
            $table->json('state_vector')->nullable(); // { "coherence": 0.8, "entropy": 0.2 ... }
            
            // Linking to the genome
            $table->foreignUuid('evolution_profile_id')->nullable()->constrained('evolution_profiles');
            
            // Phase tracking
            $table->string('current_phase')->default('stable'); // stable, critical, collapse
        });

        // 3. Governance Logs (The Intervention History)
        Schema::create('governance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('action_type'); // inject_resource, smite, mutate_genome
            $table->json('parameters')->nullable();
            $table->text('reasoning')->nullable(); // AI or Human reasoning
            $table->string('executor'); // "AI_GOD" or "Human"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_logs');
        
        Schema::table('world_states', function (Blueprint $table) {
            $table->dropForeign(['evolution_profile_id']);
            $table->dropColumn(['state_vector', 'evolution_profile_id', 'current_phase']);
        });

        Schema::dropIfExists('evolution_profiles');
    }
};
