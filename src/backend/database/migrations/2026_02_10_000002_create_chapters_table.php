<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('story_id')->constrained('stories')->cascadeOnDelete();
            $table->unsignedInteger('order'); // Chapter number
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            
            // Logic linkage (story_seeds created in next migration)
            $table->uuid('resolved_seed_id')->nullable();
            
            $table->json('generated_seeds')->nullable(); // Snapshot of new seeds created in this chapter
            
            $table->timestamps();
            
            $table->unique(['story_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
