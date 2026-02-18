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
            $table->float('entropy')->nullable()->after('state_vector');
            $table->float('stability_index')->nullable()->after('entropy');
            $table->string('status', 32)->default('running')->after('stability_index'); // running, collapsed, stable, archived
            $table->string('parent_universe_id')->nullable()->after('status');
        });

        Schema::table('universes', function (Blueprint $table) {
            $table->foreign('parent_universe_id')->references('id')->on('universes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['parent_universe_id']);
            $table->dropColumn(['entropy', 'stability_index', 'status', 'parent_universe_id']);
        });
    }
};
