<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_survival', function (Blueprint $table) {
            $table->id();
            $table->string('character_id')->unique();
            $table->float('base_survival_rate')->default(1.0);
            $table->json('risk_factors')->nullable(); // Stores RiskFactors
            $table->json('narrative_weight')->nullable(); // Stores NarrativeWeight
            $table->float('plot_armor_factor')->default(1.0);
            $table->boolean('is_alive')->default(true);
            $table->timestamp('last_survival_check')->nullable();
            $table->float('current_survival_probability')->nullable();
            $table->integer('death_tick')->nullable();
            $table->string('death_reason')->nullable();
            $table->json('death_context')->nullable();
            $table->timestamps();

            $table->index(['is_alive', 'current_survival_probability']);
            $table->index('last_survival_check');
            $table->index('death_tick');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_survival');
    }
};
