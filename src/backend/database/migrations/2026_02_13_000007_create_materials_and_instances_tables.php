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
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('ontology');
            $table->string('function');
            $table->string('default_lifecycle');
            $table->text('description');
            $table->json('origin_sources');
            $table->json('pressure_inputs');
            $table->json('pressure_outputs');
            $table->json('mutation_axes')->nullable();
            $table->json('incompatible_with')->nullable();
            $table->json('preconditions')->nullable();
            $table->timestamps();
        });

        Schema::create('material_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->integer('strength_level')->default(0);
            $table->json('mutation_state')->nullable();
            $table->integer('activation_epoch')->nullable();
            $table->json('historical_traces')->nullable();
            $table->integer('degradation_level')->default(0);
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_instances');
        Schema::dropIfExists('materials');
    }
};
