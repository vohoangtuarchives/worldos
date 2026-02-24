<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hybrid_presets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('parent_preset_a');
            $table->string('parent_preset_b');
            $table->string('hybrid_type');
            $table->json('hybrid_equations'); // Mixed evolution equations
            $table->json('collapse_conditions'); // Combined collapse conditions
            $table->decimal('interaction_strength', 5, 3); // Strength that created hybrid
            $table->integer('creation_tick');
            $table->json('identity_data'); // Name, description, characteristics
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['parent_preset_a', 'parent_preset_b']);
            $table->index('hybrid_type');
            $table->index('creation_tick');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hybrid_presets');
    }
};
