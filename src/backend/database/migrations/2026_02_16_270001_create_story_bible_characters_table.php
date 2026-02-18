<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nhân vật trong Story Bible: name, role, traits, first_seen_chapter, is_active.
     */
    public function up(): void
    {
        if (Schema::hasTable('story_bible_characters')) {
            return;
        }

        Schema::create('story_bible_characters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('story_bible_id');
            $table->string('name');
            $table->string('role')->nullable();
            $table->json('traits')->nullable();
            $table->unsignedSmallInteger('first_seen_chapter')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('story_bible_id')->references('id')->on('story_bibles')->onDelete('cascade');
            $table->index(['story_bible_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_bible_characters');
    }
};
