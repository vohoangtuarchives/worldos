<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('series_id');
            $table->integer('book_index');
            $table->integer('chapter_index');
            $table->longText('raw_text');
            $table->string('status')->default('draft');
            $table->timestamp('canonized_at')->nullable();
            $table->timestamps();

            $table->foreign('series_id')
                ->references('id')
                ->on('narrative_series')
                ->cascadeOnDelete();

            $table->index(['series_id', 'book_index', 'chapter_index']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_chapters');
    }
};
