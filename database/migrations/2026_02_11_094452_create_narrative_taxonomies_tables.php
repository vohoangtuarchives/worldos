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
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique(); // e.g., "Freedom vs Security"
                $table->jsonb('philosophical_vector'); // { "freedom": 0.8, "control": -0.8 }
                $table->jsonb('moral_axis'); // { "justice": 0.5, "survival": 0.5 }
                $table->jsonb('emotional_axis'); // { "hope": 0.2, "despair": 0.9 }
                $table->jsonb('compatible_conflicts')->nullable(); // List of conflict pattern IDs
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('conflict_patterns')) {
            Schema::create('conflict_patterns', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type'); // ideological, personal, systemic, metaphysical
                $table->string('name'); // e.g., "The Ideological War"
                $table->jsonb('escalation_curve'); // { "setup": 10, "rising": 30, "climax": 80 }
                $table->jsonb('resolution_modes'); // ["total_victory", "negotiated_peace", "mutual_destruction"]
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('power_systems')) {
            Schema::create('power_systems', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // e.g., "Blood Magic"
                $table->string('source_type'); // internal, external, contract, artifact
                $table->jsonb('cost_model'); // { "type": "lifespan", "rate": 0.1 }
                $table->float('corruption_factor')->default(0.0);
                $table->float('scaling_limit')->default(1.0);
                $table->timestamps();
            });
        }

        // Renamed to avoid conflict with existing 'archetypes' table
        if (!Schema::hasTable('character_archetypes')) {
            Schema::create('character_archetypes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // e.g., "The Reluctant Hero"
                $table->jsonb('desire_vector'); // { "power": 0.1, "peace": 0.9 }
                $table->jsonb('fear_vector'); // { "chaos": 0.8, "failure": 0.2 }
                $table->float('contradiction_index')->default(0.0);
                $table->timestamps();
            });
        }
        
        if (!Schema::hasTable('myth_symbols')) {
            Schema::create('myth_symbols', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // e.g., "The Tower"
                $table->jsonb('symbolic_axis'); // { "authority": 0.9, "isolation": 0.7 }
                $table->float('inversion_potential')->default(0.0); // How likely it is to be subverted
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('structural_patterns')) {
            Schema::create('structural_patterns', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // e.g., "Tragedy Descent"
                $table->jsonb('arc_template'); // { "stages": ["hubris", "mistake", "downfall"] }
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('narrative_taxonomies_tables');
    }
};
