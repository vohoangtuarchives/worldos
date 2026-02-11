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
        if (!Schema::hasTable('genesis_seeds')) {
            Schema::create('genesis_seeds', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->jsonb('metaphysics_vector'); // { "tinh": 0.3, "khi": 0.5, "than": 0.2 }
                $table->float('instability_index')->default(0.5);
                $table->string('seed_string')->index();
                $table->jsonb('tags')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('story_blueprints')) {
            Schema::create('story_blueprints', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('genesis_seed_id')->constrained('genesis_seeds')->cascadeOnDelete();
                
                // Foreign keys to registries
                $table->foreignUuid('theme_id')->constrained('themes');
                $table->foreignUuid('conflict_id')->constrained('conflict_patterns');
                $table->foreignUuid('power_system_id')->constrained('power_systems');
                
                // Archetypes
                $table->foreignUuid('protagonist_archetype_id')->constrained('character_archetypes');
                $table->foreignUuid('antagonist_archetype_id')->constrained('character_archetypes');
                
                $table->float('novelty_score');
                $table->jsonb('structure_vector'); // { "pacing": "fast", "tone": "grim" }
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genesis_tables');
    }
};
