<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('world_id');
            $table->json('state_vector');
            $table->json('cascade_state')->nullable();
            $table->integer('current_tick')->default(0);
            $table->integer('age')->default(0);
            $table->string('status', 20)->default('RUNNING');
            $table->bigInteger('random_seed');
            $table->uuid('parent_universe_id')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->foreign('world_id')
                ->references('id')
                ->on('worlds')
                ->onDelete('cascade');

            $table->index('world_id');
            $table->index('status');
            $table->index('parent_universe_id');
        });

        Schema::table('universes', function (Blueprint $table) {
            $table->foreign('parent_universe_id')
                ->references('id')
                ->on('universes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universes');
    }
};
