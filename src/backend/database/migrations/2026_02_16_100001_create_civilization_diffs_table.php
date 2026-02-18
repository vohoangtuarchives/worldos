<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civilization_diffs', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->unsignedBigInteger('from_tick');
            $table->unsignedBigInteger('to_tick');
            $table->json('diff_jsonb');

            $table->unique(['universe_id', 'from_tick', 'to_tick']);
            $table->index(['universe_id', 'from_tick', 'to_tick']);

            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civilization_diffs');
    }
};
