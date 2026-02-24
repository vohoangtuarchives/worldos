<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saga_worlds', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('saga_id');
            $table->uuid('world_id');
            $table->uuid('universe_id');
            $table->integer('sequence')->default(1);
            $table->timestamps();

            $table->foreign('saga_id')
                ->references('id')
                ->on('sagas')
                ->cascadeOnDelete();

            $table->foreign('world_id')
                ->references('id')
                ->on('worlds')
                ->cascadeOnDelete();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->cascadeOnDelete();

            $table->unique(['saga_id', 'universe_id']);
            $table->index(['saga_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_worlds');
    }
};
