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
            $table->json('parameters')->default(json_encode([]));
            $table->json('material_affinities')->default(json_encode([]));
            $table->json('progression_state')->default(json_encode([
                'current_stage' => 'mundane',
                'pressure' => 0,
                'stage_history' => [],
            ]));
            $table->json('collision_traits')->default(json_encode([]));
            $table->timestamps();

            $table->foreign('world_id')
                ->references('id')
                ->on('worlds')
                ->cascadeOnDelete();

            $table->unique(['world_id', 'schema_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_power_profiles');
    }
};