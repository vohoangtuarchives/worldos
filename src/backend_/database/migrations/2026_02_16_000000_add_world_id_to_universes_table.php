<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Universe = Runtime Instance of a World. world_id links instance to aggregate root.
     */
    public function up(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->foreignUuid('world_id')->nullable()->after('id')->constrained('worlds')->nullOnDelete();
            $table->index('world_id');
        });
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['world_id']);
            $table->dropColumn('world_id');
        });
    }
};
