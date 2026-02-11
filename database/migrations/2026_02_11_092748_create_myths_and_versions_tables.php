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
        Schema::create('myths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('world_id')->index();
            $table->uuid('linked_scar_id')->nullable()->index(); // Connects to physical reality
            $table->string('name')->nullable();
            
            $table->uuid('current_version_id')->nullable(); // Pointer to active version
            
            $table->float('myth_strength')->default(0.0); // How widely believed?
            $table->string('state')->default('active'); // active, rewritten, forgotten
            
            $table->integer('created_tick');
            $table->timestamps();
        });

        Schema::create('myth_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('myth_id')->index();
            $table->integer('version_number');
            
            $table->string('narrative_hash')->nullable(); // For dedup
            $table->text('content_summary'); // The story content
            $table->jsonb('ideology_pull_vector'); // { "trust": +0.1, "aggression": -0.2 }
            $table->string('rewrite_reason')->nullable();
            
            $table->integer('created_tick');
            $table->timestamps();

            $table->foreign('myth_id')->references('id')->on('myths')->onDelete('cascade');
        });

        Schema::table('myths', function (Blueprint $table) {
             $table->foreign('current_version_id')->references('id')->on('myth_versions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myths_and_versions_tables');
    }
};
