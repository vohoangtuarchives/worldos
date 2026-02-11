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
            $table->uuid('world_id')->index();
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
            $table->uuid('institution_id')->index();
            $table->string('action_type'); // HEAL, CENSOR, PROMOTE, SANCTION
            $table->jsonb('target_params'); // { "faction_id": "...", "scar_id": "..." }
            $table->float('resource_cost');
            
            $table->integer('tick');
            $table->timestamps();

            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
        });

        Schema::create('healing_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id')->index();
            $table->uuid('target_scar_id')->index();
            
            $table->float('effectiveness_score'); // How much it reduced the scar
            $table->jsonb('methodology_vector'); // { "ritual": 0.8, "propaganda": 0.2 }
            
            $table->integer('tick');
            $table->timestamps();

            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->foreign('target_scar_id')->references('id')->on('scars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions_and_healing_tables');
    }
};
