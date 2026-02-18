<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epochs', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->unsignedBigInteger('start_tick');
            $table->unsignedBigInteger('end_tick');
            $table->string('dominant_attractor', 64);
            $table->string('label', 128)->nullable();

            $table->index(['universe_id', 'start_tick']);
            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epochs');
    }
};
