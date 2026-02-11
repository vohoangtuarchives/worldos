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
        Schema::create('scars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('world_id')->index();
            $table->uuid('origin_event_id')->index(); // The event that caused this scar
            $table->uuid('faction_id')->nullable()->index(); // If specific to a faction
            $table->uuid('character_id')->nullable()->index(); // If specific to a character
            
            $table->string('wound_type'); // betrayal, loss, humility, etc.
            $table->float('pain_score'); // Raw intensity
            $table->jsonb('belief_shift_vector'); // { "trust": -0.2, "aggression": +0.1 }
            $table->float('decay_rate')->default(0.01); // Deterministic decay
            $table->string('state')->default('active'); // active, suppressed, fossilized
            
            $table->integer('created_tick');
            $table->integer('resolved_tick')->nullable();
            $table->timestamps();
        });

        Schema::create('scar_counterforces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('scar_id');
            $table->uuid('origin_event_id'); // The healing event
            $table->jsonb('healing_vector'); // Impact on the scar
            $table->float('strength');
            
            $table->integer('created_tick');
            $table->timestamps();

            $table->foreign('scar_id')->references('id')->on('scars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scars_and_counterforces_tables');
    }
};
