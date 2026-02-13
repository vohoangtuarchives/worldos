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
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->jsonb('morph_target_centroid')->nullable()->after('attractor_incarnation_id');
            $table->integer('morph_start_tick')->nullable()->after('morph_target_centroid');
            $table->float('morph_intensity')->default(1.0)->after('morph_start_tick');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->dropColumn(['morph_target_centroid', 'morph_start_tick', 'morph_intensity']);
        });
    }
};
