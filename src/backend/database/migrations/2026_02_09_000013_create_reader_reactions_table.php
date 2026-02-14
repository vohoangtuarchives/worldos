<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('reader_sessions')->cascadeOnDelete();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tick');
            $table->string('type'); // candle, flower, dread, hope
            $table->timestamps();

            $table->index(['world_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_reactions');
    }
};
