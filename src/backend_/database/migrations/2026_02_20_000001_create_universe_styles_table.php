<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('universe_styles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('style_vector'); // EntropyDecayRate, InnovationBurstProbability, etc.
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universe_styles');
    }
};
