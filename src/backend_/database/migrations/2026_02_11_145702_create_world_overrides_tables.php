<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Worlds Table (if not exists, or update)
        // Since we are building the "Vertical Slice" and assuming previous 'World' model might be from another attempt
        // We will strictly define what WE need.
        // If 'worlds' exists, we might need to add columns. 
        // Given the user context "The user's main goal is to design and implement... World OS", likely 'worlds' table from previous non-OS attempts might exist.
        // I will check if table exists and add columns, or create.
        // For simplicity and to ensure correct schema for OS, I'll assume we need to create or structurally ensure these fields.
        
        if (!Schema::hasTable('worlds')) {
            Schema::create('worlds', function (Blueprint $table) {
                $table->uuid('id')->primary();
                // We link world to a specific VERSION of a preset.
                $table->foreignUuid('preset_version_id')->constrained('preset_versions');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } else {
            // If exists, make sure it has preset_version_id
            Schema::table('worlds', function (Blueprint $table) {
                if (!Schema::hasColumn('worlds', 'preset_version_id')) {
                    $table->foreignUuid('preset_version_id')->nullable()->constrained('preset_versions');
                }
            });
        }

        // 2. World Material Overrides
        Schema::create('world_material_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            
            // Link to original material (nullable if it's a new local material)
            $table->foreignUuid('preset_material_id')->nullable()->constrained('preset_materials')->cascadeOnDelete();
            
            $table->string('override_mode'); // modify, disable, extend
            
            // For 'extend' or 'modify' (if renaming)
            $table->string('slug')->nullable(); 
            $table->string('name')->nullable();
            
            $table->json('metadata')->nullable(); // Merged or new metadata
            $table->float('power_scale_modifier')->nullable(); // Multiplier or replacement? Let's say replacement or diff. 
            // Implementation detail: Logic will decide. Storing as float.
            
            $table->string('rarity_override')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: A world can only override a specific material once? 
            // Or create one extension with a specific slug once?
            $table->unique(['world_id', 'preset_material_id']); 
            // Note: If preset_material_id is null (extension), this unique constraint might handle NULLs differently depending on DB.
            // In standard SQL, NULL != NULL. So multiple extensions allowed. 
            // But we should probably constrain slug if it's an extension.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_material_overrides');
        // We don't drop 'worlds' if we didn't definitely create it, but for this vertical slice strictness:
        // Schema::dropIfExists('worlds'); // Risky if it existed before. 
        // Let's just drop the column if we added it? 
        // For this task, I'll leave 'worlds' alone or just drop overrides.
    }
};
