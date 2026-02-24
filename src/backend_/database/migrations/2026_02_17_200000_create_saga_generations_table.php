<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saga_generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('saga_id');
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->integer('sequence')->default(0);
            $table->json('objective_vector')->nullable();
            $table->string('archetype')->nullable();
            $table->boolean('stability_flag')->default(false);
            $table->timestamps();

            $table->foreign('saga_id')->references('id')->on('sagas')->onDelete('cascade');
            $table->index(['saga_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_generations');
    }
};
