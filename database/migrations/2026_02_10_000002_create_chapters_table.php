<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order'); // Chapter number
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            
            // Logic linkage
            $table->unsignedBigInteger('resolved_seed_id')->nullable(); 
            // We can't constrain yet if seeds are created after? No, seed exists before resolution.
            // But we might resolve a seed that is not yet persisted if simulation runs in memory?
            // Let's assume seeds are persisted.
            
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
