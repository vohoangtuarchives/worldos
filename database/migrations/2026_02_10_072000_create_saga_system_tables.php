<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sagas table (meta-level orchestration)
        Schema::create('sagas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->integer('world_count')->default(0); // How many worlds to run
            $table->json('archetype_focus')->nullable(); // Which archetypes to emphasize
            $table->boolean('carry_legacy')->default(true); // Transfer legacy between worlds
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('current_world_index')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
        });

        // Saga worlds (worlds that belong to a saga)
        Schema::create('saga_worlds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('saga_id');
            $table->unsignedBigInteger('world_id');
            $table->integer('sequence')->default(0); // Order in saga
            $table->json('archetype_legacy')->nullable(); // Legacy from prev world
            $table->json('myth_legacy')->nullable(); // Myth residue carried forward
            $table->string('status')->default('pending'); // pending, running, completed, collapsed
            $table->json('collapse_context')->nullable(); // If collapsed, why
            $table->timestamps();
            
            $table->foreign('saga_id')->references('id')->on('sagas')->onDelete('cascade');
            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
            
            $table->index(['saga_id', 'sequence']);
        });

        // Saga observations (what we learned from the saga)
        Schema::create('saga_observations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('saga_id');
            $table->string('observation_type'); // pattern, divergence, archetype_shift
            $table->text('observation');
            $table->json('context')->nullable();
            $table->float('confidence')->default(0.5);
            $table->timestamps();
            
            $table->foreign('saga_id')->references('id')->on('sagas')->onDelete('cascade');
            
            $table->index('observation_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_observations');
        Schema::dropIfExists('saga_worlds');
        Schema::dropIfExists('sagas');
    }
};
