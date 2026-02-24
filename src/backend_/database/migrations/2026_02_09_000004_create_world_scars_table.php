<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_scars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            
            // Source of the scar (Myth or Event)
            $table->uuid('source_myth_id')->nullable(); 
            $table->uuid('source_event_id')->nullable();
            
            // Snapshot of the world when the scar formed (frozen history)
            $table->jsonb('snapshot_data')->nullable(); 
            
            // Physics impact
            $table->float('inertia_weight')->default(1.0);
            
            $table->timestamps();

            $table->index('world_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_scars');
    }
};
