<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WorldOS 2.0: Chronicle can be scoped to Universe when tick runs via Universe.
     */
    public function up(): void
    {
        Schema::table('chronicles', function (Blueprint $table) {
            $table->string('universe_id', 36)->nullable()->after('world_id');
            $table->index('universe_id');
        });
    }

    public function down(): void
    {
        Schema::table('chronicles', function (Blueprint $table) {
            $table->dropIndex(['universe_id']);
            $table->dropColumn('universe_id');
        });
    }
};
