<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attractor_influence_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->unsignedBigInteger('tick');
            $table->string('dominant_attractor', 64);
            $table->json('influences_jsonb');
            $table->unsignedSmallInteger('consecutive_cycles')->default(0);

            $table->unique(['universe_id', 'tick']);
            $table->index(['universe_id', 'tick']);
            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractor_influence_snapshots');
    }
};
