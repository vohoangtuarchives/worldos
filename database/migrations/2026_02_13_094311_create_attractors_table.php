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
        Schema::create('attractors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('name', 128);
            $table->uuid('current_incarnation_id')->nullable();
            $table->string('lifecycle_state', 32)->default('EMERGENT'); // EMERGENT|DOMINANT|DECLINING|EXTINCT
            $table->jsonb('historical_inertia')->nullable();
            $table->float('cumulative_rebirth_gain')->default(0.0);
            $table->float('identity_karma_index')->default(0.0);
            $table->string('phase_state', 32)->default('STABLE'); // STABLE|CHAOTIC_TRANSITION|RECONSOLIDATING
            $table->timestamps();

            $table->index('lifecycle_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attractors');
    }
};
