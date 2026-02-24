<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('narrative_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');
            $table->string('genre');
            $table->string('title');
            $table->integer('current_book_index')->default(1);
            $table->integer('total_chapters_generated')->default(0);
            $table->boolean('require_arc_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->cascadeOnDelete();

            $table->index(['universe_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_series');
    }
};
