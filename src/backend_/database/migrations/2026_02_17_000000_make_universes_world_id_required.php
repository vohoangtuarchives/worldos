<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Universe must belong to a World. Assign legacy world to any universe with null world_id, then make world_id NOT NULL.
     */
    public function up(): void
    {
        $nullCount = DB::table('universes')->whereNull('world_id')->count();
        if ($nullCount > 0) {
            $legacyWorldId = DB::table('worlds')->orderBy('id')->value('id');
            if ($legacyWorldId === null) {
                $legacyWorldId = (string) Str::uuid();
                DB::table('worlds')->insert([
                    'id' => $legacyWorldId,
                    'name' => 'Legacy (pre-world_id)',
                    'preset' => 'legacy',
                    'gene_vector' => json_encode([]),
                    'entropy' => 0.0,
                    'current_tick' => 0,
                    'autonomous' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('universes')->whereNull('world_id')->update(['world_id' => $legacyWorldId]);
        }

        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['world_id']);
            $table->uuid('world_id')->nullable(false)->change();
            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['world_id']);
            $table->uuid('world_id')->nullable()->change();
            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('set null');
        });
    }
};
