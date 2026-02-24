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
        Schema::table('world_events', function (Blueprint $table) {
            $table->string('timeline_id')->nullable()->index()->after('id');
            $table->integer('chapter')->nullable()->index()->after('timeline_id');
            // Make existing world_id nullable via change() if needed, but likely keeping it is fine.
            // However, Event Sourcing might not always need world_id if timeline_id implies it.
            // For now, let's keep world_id required and assume timeline_id is set for new events.
            $table->uuid('world_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            //
        });
    }
};
