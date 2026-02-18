<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attractor_centroid_history')) {
            return;
        }

        Schema::create('attractor_centroid_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attractor_id')->constrained('universe_attractors')->cascadeOnDelete();
            $table->unsignedBigInteger('tick');
            $table->json('centroid_jsonb');

            $table->index(['attractor_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractor_centroid_history');
    }
};
