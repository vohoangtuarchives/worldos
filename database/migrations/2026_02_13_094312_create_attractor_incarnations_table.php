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
        Schema::create('attractor_incarnations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attractor_id');
            $table->uuid('parent_incarnation_id')->nullable();
            $table->integer('start_tick');
            $table->integer('end_tick')->nullable();
            $table->jsonb('centroid_snapshot'); // {entropy, energy, stability, ...}
            $table->jsonb('semantic_snapshot')->nullable(); // {theme, archetype, mood, ...}
            $table->float('basin_radius');
            $table->float('curvature_factor');
            $table->float('rebirth_gain_from_parent')->default(0.0);
            $table->float('morph_intensity')->default(0.0);
            $table->timestamps();

            $table->foreign('attractor_id')->references('id')->on('attractors')->onDelete('cascade');
            $table->index(['attractor_id', 'start_tick']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attractor_incarnations');
    }
};
