<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Core archetypes table (immutable definitions)
        Schema::create('archetypes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // e.g., "silence", "sacrifice"
            $table->string('domain'); // perception | power | social | metaphysical
            $table->json('polarity'); // ["order", "chaos"] or ["unity", "division"]
            $table->float('baseline_weight')->default(0.5); // Initial weight
            $table->float('volatility')->default(0.3); // How easily it drifts
            $table->string('version')->default('1.0.0'); // Kernel version
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('domain');
            $table->index('version');
        });
        
        // World-specific archetype weights (mutable)
        Schema::create('archetype_weights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('archetype_key');
            $table->float('weight'); // Current weight (0-1)
            $table->float('effective_weight')->nullable(); // After bias/trauma
            $table->json('drift_history')->nullable(); // Track weight changes
            $table->timestamps();
            
            $table->unique(['world_id', 'archetype_key']);
            $table->index('archetype_key');
        });
        
        // Archetype drift log (history tracking)
        Schema::create('archetype_drift_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('archetype_key');
            $table->float('drift_delta'); // Change amount
            $table->json('drift_sources'); // Which sources contributed
            $table->integer('tick')->nullable(); // Game tick
            $table->text('context')->nullable();
            $table->timestamps();
            
            $table->index(['world_id', 'archetype_key']);
            $table->index('created_at');
        });
        
        // Archetype mutations (rare structural changes)
        Schema::create('archetype_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('parent_archetype'); // Original archetype
            $table->string('variant_1'); // First fork
            $table->string('variant_2'); // Second fork
            $table->string('trigger_type'); // EXTREME_COLLAPSE | MYTH_PARADOX | REPEATED_FAILURE
            $table->json('trigger_context'); // Collapse details, saga history, etc.
            $table->foreignUuid('origin_world_id')->nullable()->constrained('worlds')->nullOnDelete(); // Where it happened
            $table->uuid('origin_saga_id')->nullable(); // Which saga
            $table->boolean('irreversible')->default(true);
            $table->timestamps();
            
            $table->index('parent_archetype');
            $table->index('trigger_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archetype_mutations');
        Schema::dropIfExists('archetype_drift_log');
        Schema::dropIfExists('archetype_weights');
        Schema::dropIfExists('archetypes');
    }
};
