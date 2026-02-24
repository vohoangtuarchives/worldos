<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_power_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('schema_key');
            $table->json('parameters')->nullable();
            $table->json('material_affinities')->nullable();
            $table->json('progression_state')->nullable();
            $table->json('collision_traits')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['world_id', 'schema_key']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_power_profiles');
    }
};