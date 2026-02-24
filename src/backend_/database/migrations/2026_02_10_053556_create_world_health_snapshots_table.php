<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_health_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('health_status'); // STABLE, DEGRADED, CRITICAL, HALTED
            $table->integer('health_score')->nullable(); // Optional numeric score
            $table->unsignedBigInteger('tick')->nullable(); // Current tick
            $table->json('metadata')->nullable(); // Additional context (e.g., alert count)
            $table->timestamp('recorded_at');
            
            $table->index(['world_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_health_snapshots');
    }
};
