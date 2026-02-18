<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turning_points', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->unsignedBigInteger('tick');
            $table->string('type', 32); // dominant_shift | mutation
            $table->json('payload')->nullable();

            $table->index(['universe_id', 'tick']);
            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turning_points');
    }
};
