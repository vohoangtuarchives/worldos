<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_seeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('story_id')->constrained('stories')->cascadeOnDelete();
            
            $table->string('type');      // POWER_GAP, SOCIAL_PRESSURE, etc.
            $table->string('dimension'); // personal, family, world...
            $table->unsignedInteger('severity')->default(1);
            $table->unsignedInteger('age')->default(0);
            
            $table->string('status')->default('active'); // active, resolved, discarded
            
            $table->timestamps();
            
            $table->index(['story_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_seeds');
    }
};
