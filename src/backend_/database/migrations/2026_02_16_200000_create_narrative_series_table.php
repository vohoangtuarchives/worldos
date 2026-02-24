<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Truyện dài kỳ (serial): Harry Potter, Tiếu Ngạo Giang Hồ style.
     * Mỗi series có genre preset, config (số "tập"/book), và có thể gắn universe.
     */
    public function up(): void
    {
        if (Schema::hasTable('narrative_series')) {
            return;
        }

        Schema::create('narrative_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('genre_key', 64)->default('fantasy_school'); // fantasy_school | wuxia | ...
            $table->string('universe_id', 36)->nullable();
            $table->json('config')->nullable(); // protagonist_seed, books_count, overarching_goal, ...
            $table->unsignedSmallInteger('current_book_index')->default(0);
            $table->unsignedInteger('total_chapters_generated')->default(0);
            $table->timestamps();
            $table->index(['genre_key', 'universe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_series');
    }
};
