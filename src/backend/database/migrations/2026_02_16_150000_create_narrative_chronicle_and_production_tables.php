<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('narrative_chronicle_events')) {
            Schema::create('narrative_chronicle_events', function (Blueprint $table) {
                $table->id();
                $table->string('source_type', 32)->default('cosmology'); // cosmology, saga
                $table->string('source_id', 36)->nullable();
                $table->string('event_type', 64);
                $table->unsignedBigInteger('tick')->nullable();
                $table->string('state_ref', 255)->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->index(['source_type', 'source_id', 'tick']);
            });
        }

        if (!Schema::hasTable('narrative_chapter_blueprints')) {
            Schema::create('narrative_chapter_blueprints', function (Blueprint $table) {
                $table->id();
                $table->string('arc_id', 36); // narrative_story_arcs.id or similar
                $table->unsignedSmallInteger('chapter_index');
                $table->string('emotional_objective', 255)->nullable();
                $table->json('conflict_delta')->nullable();
                $table->json('motif_targets')->nullable();
                $table->timestamps();
                $table->index(['arc_id', 'chapter_index']);
            });
        }

        if (!Schema::hasTable('narrative_chapter_drafts')) {
            Schema::create('narrative_chapter_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blueprint_id')->constrained('narrative_chapter_blueprints')->cascadeOnDelete();
                $table->longText('content');
                $table->unsignedSmallInteger('version')->default(1);
                $table->timestamps();
                $table->index(['blueprint_id', 'version']);
            });
        }

        if (!Schema::hasTable('narrative_evaluation_scores')) {
            Schema::create('narrative_evaluation_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('draft_id')->constrained('narrative_chapter_drafts')->cascadeOnDelete();
                $table->float('score');
                $table->json('rubric_scores')->nullable();
                $table->json('weak_points')->nullable();
                $table->timestamps();
                $table->index('draft_id');
            });
        }

        if (!Schema::hasTable('narrative_motif_registry')) {
            Schema::create('narrative_motif_registry', function (Blueprint $table) {
                $table->id();
                $table->string('story_ref', 36)->nullable(); // arc_id or story_id
                $table->string('symbol', 128);
                $table->string('visual_anchor', 255)->nullable();
                $table->string('wound_theme', 255)->nullable();
                $table->timestamps();
                $table->index('story_ref');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_evaluation_scores');
        Schema::dropIfExists('narrative_chapter_drafts');
        Schema::dropIfExists('narrative_chapter_blueprints');
        Schema::dropIfExists('narrative_motif_registry');
        Schema::dropIfExists('narrative_chronicle_events');
    }
};
