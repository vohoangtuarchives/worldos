<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WorldOS v3: Universe as runtime authority. Add entropy, stability_index, status, parent_universe_id.
     * tick = age (already exists as age); we add explicit entropy/stability for quick read; status for lifecycle.
     */
    public function up(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            if (!Schema::hasColumn('universes', 'entropy')) {
                $table->float('entropy')->nullable()->after('state_vector');
            }
            if (!Schema::hasColumn('universes', 'stability_index')) {
                $table->float('stability_index')->nullable()->after('entropy');
            }
            if (!Schema::hasColumn('universes', 'status')) {
                $table->string('status', 32)->default('running')->after('stability_index');
            }
            if (!Schema::hasColumn('universes', 'parent_universe_id')) {
                $table->string('parent_universe_id')->nullable()->after('status');
            }
        });

        // We skip the foreign key if column already existed as it might already have the FK
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['parent_universe_id']);
            $table->dropColumn(['entropy', 'stability_index', 'status', 'parent_universe_id']);
        });
    }
};
