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
            $table->uuid('world_id');
            $table->string('schema_key');
            $table->json('parameters')->nullable();
            $table->json('material_affinities')->nullable();
            $table->json('progression_state')->nullable();
            $table->json('collision_traits')->nullable();
            $table->timestamps();

            // Remove foreign key for now - will add later in separate migration
            $table->index('world_id');
            $table->unique(['world_id', 'schema_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_power_profiles');
    }
};