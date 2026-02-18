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
        Schema::create('interaction_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attractor_a_id');
            $table->uuid('attractor_b_id');
            $table->float('shared_survival')->default(0.0);
            $table->float('conflict_intensity')->default(0.0);
            $table->float('rebirth_alignment')->default(0.0);
            $table->float('hr_score')->default(0.0); // Historical Resonance
            $table->timestamps();

            $table->foreign('attractor_a_id')->references('id')->on('attractors')->onDelete('cascade');
            $table->foreign('attractor_b_id')->references('id')->on('attractors')->onDelete('cascade');
            $table->index(['attractor_a_id', 'attractor_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_histories');
    }
};
