<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_emotions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('type'); // anger, fear, joy, trust...
            $table->float('intensity'); // 0.0 to 1.0
            $table->float('decay_rate')->default(0.1);
            $table->timestamps();

            // Ensure one row per emotion type per character
            $table->unique(['character_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_emotions');
    }
};
