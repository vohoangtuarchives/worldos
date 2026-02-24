<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vietnamese_heroes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 200);
            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            $table->integer('era')->nullable();
            $table->string('period', 50)->index(); // 'MYTHICAL', 'BAC_THUOC', etc.
            $table->string('region', 100)->nullable();
            $table->string('archetype', 100);
            $table->string('cosmic_role', 100)->nullable();
            $table->text('biography')->nullable();
            $table->text('quote')->nullable();
            
            // Calculated dimension scores (0.0 - 1.0)
            $table->decimal('military', 3, 2)->default(0);
            $table->decimal('governance', 3, 2)->default(0);
            $table->decimal('territory', 3, 2)->default(0);
            $table->decimal('philosophy', 3, 2)->default(0);
            $table->decimal('education', 3, 2)->default(0);
            $table->decimal('culture', 3, 2)->default(0);
            $table->decimal('spirituality', 3, 2)->default(0);
            $table->decimal('rebellion', 3, 2)->default(0);
            $table->decimal('reform', 3, 2)->default(0);
            $table->decimal('diplomacy', 3, 2)->default(0);
            $table->decimal('economic', 3, 2)->default(0);
            $table->decimal('mythic', 3, 2)->default(0);
            
            // Impact score
            $table->decimal('impact_score', 4, 2)->default(0)->index();
            
            // Scoring metadata
            $table->uuid('scoring_version_id')->nullable();
            $table->timestamp('last_scored_at')->nullable();
            $table->string('status', 20)->default('active')->index();
            
            $table->timestamps();
            
            $table->index('era');
            $table->index(['period', 'impact_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vietnamese_heroes');
    }
};
