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
            $table->jsonb('social_classes')->nullable()->after('civ_resilience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->dropColumn('social_classes');
        });
    }
};
