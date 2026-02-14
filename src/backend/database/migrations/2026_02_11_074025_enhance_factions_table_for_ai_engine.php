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
        Schema::table('factions', function (Blueprint $table) {
            $table->jsonb('leader_data')->nullable(); // Stores Leader VO
            $table->jsonb('ideology_vector')->nullable(); // Stores IdeologyVector VO
            $table->jsonb('personality_vector')->nullable(); // Stores PersonalityVector VO (current faction personality)
            $table->jsonb('memory_state')->nullable(); // Stores FactionMemory VO
            $table->integer('current_generation')->default(1);
            $table->float('internal_cohesion')->default(1.0);
        });

        Schema::create('faction_history_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faction_id')->constrained()->onDelete('cascade');
            $table->integer('turn');
            $table->string('intent_type');
            $table->jsonb('reasoning')->nullable(); // Why the agent made this choice
            $table->float('outcome_score')->nullable(); // Reward/Penalty
            $table->timestamps();

            $table->index(['faction_id', 'turn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faction_history_logs');
        
        Schema::table('factions', function (Blueprint $table) {
            $table->dropColumn([
                'leader_data',
                'ideology_vector',
                'personality_vector',
                'memory_state',
                'current_generation',
                'internal_cohesion'
            ]);
        });
    }
};
