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
        // Table for atomic narrative elements (Seeds)
        if (!Schema::hasTable('narrative_material_seeds')) {
            Schema::create('narrative_material_seeds', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type'); // power_system, social_structure, twist, environment, object
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('attributes')->nullable(); // e.g., {"complexity": "high", "tone": "dark"}
                $table->json('compatibility_tags')->nullable(); // Tags for matching logic
                $table->timestamps();
            });
        }

        // Table for generated story concepts (Premises)
        if (!Schema::hasTable('narrative_story_premises')) {
            Schema::create('narrative_story_premises', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->text('summary')->nullable(); // Generated pitch
                $table->json('components'); // Array of seed IDs used
                $table->json('power_escalation')->nullable(); // Tier definitions (Human -> Mythic)
                $table->boolean('is_favorite')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('narrative_story_premises');
        Schema::dropIfExists('narrative_material_seeds');
    }
};
