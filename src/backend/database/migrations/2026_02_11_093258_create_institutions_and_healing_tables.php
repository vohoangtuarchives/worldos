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
        Schema::create('institutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // religious, academic, military, bureaucracy
            
            // Leaderless structure
            $table->jsonb('charter_values'); // Ideological anchor { "purity": 0.9, "militarism": 0.1 }
            $table->float('public_trust')->default(0.5);
            $table->float('authority_level')->default(0.5);
            
            $table->integer('created_tick');
            $table->timestamps();
        });

        Schema::create('institutional_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('action_type'); // HEAL, CENSOR, PROMOTE, SANCTION
            $table->jsonb('target_params'); // { "faction_id": "...", "scar_id": "..." }
            $table->float('resource_cost');
            
            $table->integer('tick');
            $table->timestamps();
        });

        Schema::create('healing_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignUuid('target_scar_id')->constrained('scars')->cascadeOnDelete();
            
            $table->float('effectiveness_score'); // How much it reduced the scar
            $table->jsonb('methodology_vector'); // { "ritual": 0.8, "propaganda": 0.2 }
            
            $table->integer('tick');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healing_events');
        Schema::dropIfExists('institutional_actions');
        Schema::dropIfExists('institutions');
    }
};
