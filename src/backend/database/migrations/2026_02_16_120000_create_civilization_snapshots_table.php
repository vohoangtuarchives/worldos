<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civilization_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('universe_id');
            $table->unsignedBigInteger('tick');
            $table->string('stage', 64)->nullable();
            $table->float('pressure')->nullable();
            $table->json('state_jsonb');

            $table->unique(['universe_id', 'tick']);
            $table->index(['universe_id', 'tick']);

            $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civilization_snapshots');
    }
};
