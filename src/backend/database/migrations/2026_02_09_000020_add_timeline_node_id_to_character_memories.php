<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_memories', function (Blueprint $table) {
            $table->uuid('timeline_node_id')->nullable()->after('embedding');
            // We can't easily add a foreign key constraint to timeline_nodes yet 
            // because character_memories might be created before timeline nodes in existing data,
            // or we might want memories to exist outside strict timeline nodes (global facts).
            // But for now, let's add the index.
            $table->index('timeline_node_id');
        });
    }

    public function down(): void
    {
        Schema::table('character_memories', function (Blueprint $table) {
            $table->dropColumn('timeline_node_id');
        });
    }
};
