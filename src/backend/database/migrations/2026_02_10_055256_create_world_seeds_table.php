<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_seeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seed_template_id')->constrained()->cascadeOnDelete();
            $table->string('state')->default('DORMANT'); // DORMANT, ACTIVE, EXHAUSTED
            $table->unsignedBigInteger('activation_tick')->nullable();
            $table->unsignedBigInteger('exhaustion_tick')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();
            
            $table->index(['world_id', 'state']);
            $table->index('activation_tick');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_seeds');
    }
};
