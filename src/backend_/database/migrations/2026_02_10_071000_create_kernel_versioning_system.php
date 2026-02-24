<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kernel versions table (track kernel evolution)
        Schema::create('kernel_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version')->unique(); // "1.0.0"
            $table->json('archetype_snapshot'); // All archetypes at this version
            $table->json('law_snapshot')->nullable(); // All world laws at this version
            $table->json('coupling_rules')->nullable(); // Archetype-Law coupling rules
            $table->text('release_notes')->nullable();
            $table->timestamp('released_at');
            $table->timestamps();
            
            $table->index('version');
            $table->index('released_at');
        });

        // Note: World Law coupling will be added when world_laws table is created
        // For now, coupling rules are stored in kernel_versions.coupling_rules
    }

    public function down(): void
    {
        Schema::dropIfExists('kernel_versions');
    }
};
