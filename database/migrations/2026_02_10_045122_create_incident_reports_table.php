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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id')->unique(); // e.g., INC-20261002-001
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            
            // Lifecycle
            $table->string('status')->default('DETECTED'); // DETECTED, CONTAINED, STABILIZED, ANALYZED, RESOLVED
            $table->string('severity'); // CRITICAL, HIGH, MEDIUM, LOW
            
            // 1. Summary
            $table->text('summary')->nullable();
            
            // 2. Impact Assessment (JSON)
            // { world_law: 'SEVERE', economy: 'PARTIAL', narrative: 'STABLE', replay: 'DIVERGED' }
            $table->json('impact_assessment')->nullable();
            
            // 3. Timeline (JSON)
            // [ { time: 'T-30m', event: 'Warning' }, ... ]
            $table->json('timeline_events')->nullable();
            
            // 4. Root Cause
            $table->string('root_cause')->nullable(); // AI_BEHAVIOR, LAW_GAP, etc.
            
            // 5. 5 Whys (JSON)
            // [ "Why 1...", "Why 2..." ]
            $table->json('five_whys')->nullable();
            
            // 6. Resolution
            $table->text('resolution_justification')->nullable();
            
            // 7. Action Items (JSON)
            // [ { action: "Fix logic", owner: "System", deadline: "+7d" } ]
            $table->json('action_items')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
