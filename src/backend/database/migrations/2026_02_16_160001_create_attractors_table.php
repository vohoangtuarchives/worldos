<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-universe attractor centroids (drift/mutation). Table "attractors" already exists
     * from 2026_02_13 (Cosmic domain), so this schema uses universe_attractors.
     */
    public function up(): void
    {
        if (Schema::hasTable('universe_attractors')) {
            return;
        }

        Schema::create('universe_attractors', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->string('name', 64);
            $table->json('centroid_jsonb');
            $table->json('origin_centroid_jsonb');
            $table->unsignedBigInteger('birth_tick')->default(0);
            $table->unsignedInteger('mutation_count')->default(0);
            $table->boolean('active')->default(true);

            $table->unique(['universe_id', 'name']);
            $table->index(['universe_id']);
            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_attractors');
    }
};
