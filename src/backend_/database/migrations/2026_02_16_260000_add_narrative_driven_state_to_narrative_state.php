<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Narrative-driven world state (shadow_presence, magic_stability, threat_level)
     * updated from extracted story events; injected into next chapter prompt.
     */
    public function up(): void
    {
        if (!Schema::hasTable('narrative_state')) {
            return;
        }
        if (Schema::hasColumn('narrative_state', 'narrative_driven_state')) {
            return;
        }
        Schema::table('narrative_state', function (Blueprint $table) {
            $table->json('narrative_driven_state')->nullable()->after('world_snapshot');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('narrative_state', 'narrative_driven_state')) {
            Schema::table('narrative_state', function (Blueprint $table) {
                $table->dropColumn('narrative_driven_state');
            });
        }
    }
};
