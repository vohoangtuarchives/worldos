<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        
        // Drop dependent tables first
        Schema::dropIfExists('belief_myth');
        Schema::dropIfExists('world_contradiction_memories');
        Schema::dropIfExists('world_scars');
        Schema::dropIfExists('world_myths');
        
        Schema::enableForeignKeyConstraints();

        // 1. World Myths (The Immutable Truths)
        Schema::create('world_myths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id');
            $table->text('truth_statement'); // "Dragons are extinct"
            $table->decimal('rigidity', 3, 2)->default(1.0); // 1.0 = Absolute, 0.5 = Rumor
            $table->string('origin_event_id')->nullable();
            $table->timestamps();

            $table->index('world_id');
        });

        // 2. World Scars (The Permanent Damage)
        Schema::create('world_scars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id');
            $table->string('location_scope')->nullable(); // "Tokyo", "Global"
            $table->text('constraint_rule'); // "No magic here"
            $table->decimal('severity', 3, 2);
            $table->string('origin_event_id')->nullable();
            $table->timestamps();

            $table->index('world_id');
        });

        // 3. Contradiction Memories (The AI Learning)
        Schema::create('world_contradiction_memories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id');
            $table->string('contradiction_id'); // "myth_vs_scar_01"
            $table->string('strategy_used'); // "deflection", "sacrifice"
            $table->decimal('effectiveness', 3, 2)->nullable();
            $table->string('context_hash')->index(); // Hash of involved components for lookup
            $table->timestamps();

            $table->index('world_id');
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        
        // Drop in reverse order
        Schema::dropIfExists('belief_myth');
        Schema::dropIfExists('world_contradiction_memories');
        Schema::dropIfExists('world_scars');
        Schema::dropIfExists('world_myths');
        
        Schema::enableForeignKeyConstraints();
    }
};
