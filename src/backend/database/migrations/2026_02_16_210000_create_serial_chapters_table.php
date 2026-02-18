<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lưu từng chương đã sinh của truyện dài kỳ (serial).
     * Mỗi bản ghi = một chương thuộc một series, có thể kèm summary để build story_so_far.
     */
    public function up(): void
    {
        if (Schema::hasTable('serial_chapters')) {
            return;
        }

        Schema::create('serial_chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('narrative_series_id');
            $table->unsignedSmallInteger('book_index');
            $table->unsignedSmallInteger('chapter_index');
            $table->longText('content');
            $table->text('summary')->nullable(); // 1–2 câu để ghép story_so_far
            $table->timestamps();

            $table->unique(['narrative_series_id', 'book_index', 'chapter_index']);
            $table->foreign('narrative_series_id')->references('id')->on('narrative_series')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_chapters');
    }
};
