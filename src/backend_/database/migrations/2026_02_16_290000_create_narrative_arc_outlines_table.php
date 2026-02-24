<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Outline cấp saga / season / arc. SerialArcPlanner đọc từ đây nếu có; fallback genre/evolution.
     */
    public function up(): void
    {
        if (Schema::hasTable('narrative_arc_outlines')) {
            return;
        }

        Schema::create('narrative_arc_outlines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('narrative_series_id');
            $table->string('level', 32); // saga | season | arc
            $table->unsignedSmallInteger('index')->default(0);
            $table->string('title')->nullable();
            $table->text('one_line')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 32)->default('draft'); // draft | pending_review | approved
            $table->timestamps();
            $table->foreign('narrative_series_id')->references('id')->on('narrative_series')->onDelete('cascade');
            $table->index(['narrative_series_id', 'level', 'index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_arc_outlines');
    }
};
