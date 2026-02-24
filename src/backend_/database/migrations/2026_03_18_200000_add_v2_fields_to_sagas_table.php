<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WorldOS v3 Saga v2: strategy, evaluation_policy; optional current_universe_id.
     * Deprecate world_count/current_world_index in new logic; keep columns.
     */
    public function up(): void
    {
        Schema::table('sagas', function (Blueprint $table) {
            $table->string('strategy', 64)->nullable()->after('metadata');
            $table->json('evaluation_policy')->nullable()->after('strategy');
            $table->string('current_universe_id')->nullable()->after('evaluation_policy');
        });

        Schema::table('sagas', function (Blueprint $table) {
            $table->foreign('current_universe_id')->references('id')->on('universes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sagas', function (Blueprint $table) {
            $table->dropForeign(['current_universe_id']);
            $table->dropColumn(['strategy', 'evaluation_policy', 'current_universe_id']);
        });
    }
};
