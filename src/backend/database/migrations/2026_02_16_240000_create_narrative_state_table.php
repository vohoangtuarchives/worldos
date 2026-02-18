<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Structural fingerprint per series: arc_progress, last_beat, tension, optional world snapshot.
     */
    public function up(): void
    {
        if (Schema::hasTable('narrative_state')) {
            return;
        }

        Schema::create('narrative_state', function (Blueprint $table) {
            $table->id();
            $table->uuid('narrative_series_id')->unique();
            $table->float('arc_progress')->default(0);
            $table->string('last_emotional_beat', 64)->default('');
            $table->float('last_tension')->default(0);
            $table->unsignedSmallInteger('foreshadow_cooldown')->default(0);
            $table->json('world_snapshot')->nullable(); // entropy, threat_index when universe linked
            $table->timestamps();
            $table->foreign('narrative_series_id')->references('id')->on('narrative_series')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_state');
    }
};
