<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
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
