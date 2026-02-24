<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_myths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');
            $table->string('theme');
            $table->text('description');
            $table->float('strength'); // 0.0 - 1.0
            $table->string('state')->default('active'); // active, decaying, merged
            $table->integer('tick_emerged');
            $table->json('belief_sources')->nullable();
            $table->timestamps();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->cascadeOnDelete();

            $table->index(['universe_id', 'state']);
            $table->index(['universe_id', 'theme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_myths');
    }
};
