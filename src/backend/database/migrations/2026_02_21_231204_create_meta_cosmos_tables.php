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
        if (!Schema::hasTable('universes')) {
            Schema::create('universes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('world_id')->index(); // Link to LawGenome
                $table->integer('generation')->default(0);
                $table->float('fitness')->default(0.0);
                $table->json('state')->nullable();
                $table->integer('year')->default(0);
                $table->json('parameters')->nullable(); // CouplingMatrix, Policy weights, etc.
                $table->timestamps();

                $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
            });
        } else {
            Schema::table('universes', function (Blueprint $table) {
                if (!Schema::hasColumn('universes', 'generation')) {
                    $table->integer('generation')->default(0);
                }
                if (!Schema::hasColumn('universes', 'fitness')) {
                    $table->float('fitness')->default(0.0);
                }
                // Ensure world_id is present if not already (it should be from previous migrations)
                if (!Schema::hasColumn('universes', 'world_id')) {
                    $table->uuid('world_id')->nullable()->index();
                }
            });
        }

        Schema::create('meta_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('world_id')->index();
            $table->integer('current_generation')->default(0);
            $table->string('status'); // WAITING, SIMULATING, AGGREGATING, COMPLETED
            $table->json('payload')->nullable(); // Universe IDs and their simulation results/status
            $table->timestamps();

            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_cycles');
        Schema::dropIfExists('universes');
    }
};
