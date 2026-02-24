<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Story Bible: 1-1 với narrative_series. Braindump/synopsis/style_notes cho source of truth (Sudowrite-style).
     */
    public function up(): void
    {
        if (Schema::hasTable('story_bibles')) {
            return;
        }

        Schema::create('story_bibles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('narrative_series_id');
            $table->text('braindump')->nullable();
            $table->text('synopsis')->nullable();
            $table->text('style_notes')->nullable();
            $table->timestamps();
            $table->unique('narrative_series_id');
            $table->foreign('narrative_series_id')->references('id')->on('narrative_series')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_bibles');
    }
};
