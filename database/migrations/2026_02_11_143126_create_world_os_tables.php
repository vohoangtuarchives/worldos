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
        Schema::create('world_presets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            
            $table->string('power_policy', 100);
            $table->string('resource_policy', 100);
            $table->string('conflict_policy', 100);
            $table->string('escalation_policy', 100);
            $table->string('myth_policy', 100)->nullable();
            $table->string('scar_policy', 100)->nullable();

            $table->json('config');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('world_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('preset_id');
            
            $table->integer('version');
            $table->bigInteger('seed');
            
            $table->json('snapshot');
            
            $table->uuid('parent_state_id')->nullable();
            
            $table->timestamp('created_at');

            // FKs
            $table->foreign('preset_id')->references('id')->on('world_presets');
            $table->foreign('parent_state_id')->references('id')->on('world_states');

            // Indexes
            $table->index(['preset_id', 'version']);
            $table->index('parent_state_id');
        });

        Schema::create('world_state_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_state_id');
            $table->uuid('to_preset_id');
            
            $table->string('transition_policy', 100);
            $table->text('reason')->nullable();
            
            $table->timestamp('created_at');

            $table->foreign('from_state_id')->references('id')->on('world_states');
            $table->foreign('to_preset_id')->references('id')->on('world_presets');
        });

        Schema::create('world_state_metrics', function (Blueprint $table) {
            $table->uuid('world_state_id')->primary();
            
            $table->string('strongest_character_id')->nullable();
            $table->decimal('total_power', 20, 2)->default(0);
            $table->integer('active_conflict_count')->default(0);
            $table->decimal('myth_density', 8, 4)->default(0);
            
            $table->timestamp('computed_at');

            $table->foreign('world_state_id')->references('id')->on('world_states')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_state_metrics');
        Schema::dropIfExists('world_state_transitions');
        Schema::dropIfExists('world_states');
        Schema::dropIfExists('world_presets');
    }
};
