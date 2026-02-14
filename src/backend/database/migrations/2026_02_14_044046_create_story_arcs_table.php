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
        if (!Schema::hasTable('narrative_story_arcs')) {
            Schema::create('narrative_story_arcs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('premise_id'); // Link to StoryPremise
                $table->string('title');
                $table->string('arc_type'); // Hero's Journey, 3-Act, Kishotenketsu
                $table->json('structure'); // Outline data
                $table->integer('estimated_chapters');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('narrative_story_arcs');
    }
};
