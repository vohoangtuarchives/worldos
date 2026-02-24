<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('active'); // active, completed, paused
            
            // Engine State Snapshots
            $table->json('world_state')->nullable(); // Tier, Awareness, Centers
            $table->json('character_state')->nullable(); // Tier, Exposure, ChaptersInTier
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
