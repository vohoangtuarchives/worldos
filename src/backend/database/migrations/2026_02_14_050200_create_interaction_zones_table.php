<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interaction_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('zone_coherence', 5, 3);
            $table->json('dominant_narratives'); // Array of preset types
            $table->json('active_worlds'); // Array of world IDs in zone
            $table->json('active_interactions'); // Interactions within zone
            $table->integer('formation_tick');
            $table->integer('collapse_tick')->nullable();
            $table->json('zone_metrics')->nullable(); // Additional zone data
            
            $table->timestamps();
            
            $table->index('formation_tick');
            $table->index('collapse_tick');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interaction_zones');
    }
};
