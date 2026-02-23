<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds world_signature_hash to universes table.
 *
 * Enforces the Ignite contract: every Universe must record the exact
 * WorldSignature (physics + narrative hash) that was active at the moment
 * of its creation. This guarantees deterministic replay integrity.
 *
 * Nullable to remain backward-compatible with any seed data created
 * before the Ignite process was introduced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->string('world_signature_hash', 64)
                  ->nullable()
                  ->after('world_blueprint_id')
                  ->comment('SHA-256 hash of the World Blueprint (PhysicsCore + NarrativeTopology) frozen at Ignite time.');

            $table->index('world_signature_hash');
        });
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropIndex(['world_signature_hash']);
            $table->dropColumn('world_signature_hash');
        });
    }
};
