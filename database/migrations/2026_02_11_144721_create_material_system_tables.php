<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Preset Versions
        Schema::create('preset_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('preset_id')->constrained('world_presets')->cascadeOnDelete();
            $table->string('version_label'); // v1, v2
            $table->uuid('parent_version_id')->nullable();
            $table->string('status')->default('draft'); // draft, active, archived
            
            // Moved policies from world_presets to here
            $table->string('power_policy');
            $table->string('resource_policy');
            $table->string('conflict_policy');
            $table->string('escalation_policy');
            $table->string('myth_policy')->nullable();
            $table->string('scar_policy')->nullable();
            
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['preset_id', 'version_label']);
        });

        // 2. Ontology Nodes
        Schema::create('ontology_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('preset_version_id')->constrained('preset_versions')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->string('slug');
            $table->string('name');
            $table->string('path')->nullable(); // Materialized path: power.elemental.fire
            $table->integer('depth')->default(0);
            $table->timestamps();
            
            $table->index(['preset_version_id', 'path']);
        });

        // 3. Preset Materials
        Schema::create('preset_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('preset_version_id')->constrained('preset_versions')->cascadeOnDelete();
            $table->string('type'); // spell, artifact
            $table->string('slug');
            $table->string('name');
            $table->json('metadata')->nullable();
            $table->float('power_scale')->default(0);
            $table->string('rarity')->default('common');
            $table->timestamps();

            $table->unique(['preset_version_id', 'slug']);
        });

        // 4. Pivot: Material <-> Ontology
        Schema::create('preset_material_ontology', function (Blueprint $table) {
            $table->foreignUuid('preset_material_id')->constrained('preset_materials')->cascadeOnDelete();
            $table->foreignUuid('ontology_node_id')->constrained('ontology_nodes')->cascadeOnDelete();
            $table->primary(['preset_material_id', 'ontology_node_id']);
        });

        // 5. Material Drafts (AI)
        Schema::create('material_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('preset_version_id')->constrained('preset_versions')->cascadeOnDelete();
            $table->json('payload');
            $table->json('proposed_ontology_nodes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });

        // 6. Update World States
        Schema::table('world_states', function (Blueprint $table) {
            $table->foreignUuid('preset_version_id')->nullable()->constrained('preset_versions');
        });
    }

    public function down(): void
    {
        Schema::table('world_states', function (Blueprint $table) {
            $table->dropForeign(['preset_version_id']);
            $table->dropColumn('preset_version_id');
        });
        Schema::dropIfExists('material_drafts');
        Schema::dropIfExists('preset_material_ontology');
        Schema::dropIfExists('preset_materials');
        Schema::dropIfExists('ontology_nodes');
        Schema::dropIfExists('preset_versions');
        Schema::dropIfExists('material_system_tables'); // In case it was created by the simplified artisan command
    }
};
