<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WorldOS 2.0: Cosmic snapshot can be scoped to Universe when produced from Universe tick.
     */
    public function up(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->string('universe_id', 36)->nullable()->after('world_id');
            $table->index('universe_id');
        });
    }

    public function down(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->dropIndex(['universe_id']);
            $table->dropColumn('universe_id');
        });
    }
};
