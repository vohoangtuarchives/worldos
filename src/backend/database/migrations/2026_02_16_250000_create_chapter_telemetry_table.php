<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Telemetry per chapter: beat, tension, word_count, token usage for ROI / arc integrity.
     */
    public function up(): void
    {
        if (Schema::hasTable('chapter_telemetry')) {
            return;
        }

        Schema::create('chapter_telemetry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serial_chapter_id');
            $table->uuid('narrative_series_id');
            $table->string('emotional_beat', 64)->nullable();
            $table->float('tension')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->foreign('serial_chapter_id')->references('id')->on('serial_chapters')->cascadeOnDelete();
            $table->foreign('narrative_series_id')->references('id')->on('narrative_series')->cascadeOnDelete();
            $table->index(['narrative_series_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_telemetry');
    }
};
