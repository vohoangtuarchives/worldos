<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_heroes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            
            // Hero Details
            $table->string('name');
            $table->json('other_names')->nullable();
            $table->string('archetype'); // FOUNDING_KING, etc.
            
            // Stats
            $table->json('dimensions')->nullable(); // { military: 0.9, ... }
            $table->float('impact_score')->default(0);
            
            // Narrative
            $table->text('biography')->nullable();
            $table->string('era')->nullable();
            
            // Meta
            $table->uuid('origin_hero_id')->nullable(); // Link to seed template if applicable
            $table->boolean('is_generated')->default(false);
            $table->string('status')->default('active'); // active, dead, retired
            $table->unsignedBigInteger('spawned_at_tick')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_heroes');
    }
};
