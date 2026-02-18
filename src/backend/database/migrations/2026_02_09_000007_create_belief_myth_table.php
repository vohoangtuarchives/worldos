<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('belief_myth', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('belief_id')->constrained('world_beliefs')->cascadeOnDelete();
            $table->foreignUuid('myth_id')->constrained('world_myths')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('belief_myth');
    }
};
