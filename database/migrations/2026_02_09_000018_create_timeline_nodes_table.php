<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->json('parent_ids')->nullable(); // JSON array of UUIDs
            $table->string('canonical_level')->default('MAIN'); // MAIN, ALTERNATE, DRAFT
            $table->json('state_snapshot')->nullable(); // Snapshot of global flags/state
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_nodes');
    }
};
