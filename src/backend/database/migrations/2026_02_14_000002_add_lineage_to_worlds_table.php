<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            // Lineage
            $table->foreignId('lineage_root_id')->nullable()->constrained('worlds')->nullOnDelete();
            $table->integer('generation')->default(0);
            
            // Prophet Status
            $table->boolean('is_prophet')->default(false);
            $table->jsonb('prophet_metadata')->nullable(); // { origin_myth_id, sacred_archetype_id }
            
            // Optimization
            $table->index(['lineage_root_id', 'generation']);
            $table->index('is_prophet');
        });
    }

    public function down(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->dropIndex(['is_prophet']);
            $table->dropIndex(['lineage_root_id', 'generation']);
            $table->dropColumn(['prophet_metadata', 'is_prophet', 'generation', 'lineage_root_id']);
        });
    }
};
