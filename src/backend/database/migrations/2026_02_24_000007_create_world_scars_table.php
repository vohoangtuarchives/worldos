<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_scars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');
            $table->string('source_event');
            $table->string('type');
            $table->integer('weight'); // 1-10
            $table->text('description');
            $table->integer('tick_created');
            $table->float('current_intensity')->default(1.0);
            $table->timestamps();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->cascadeOnDelete();

            $table->index(['universe_id', 'type']);
            $table->index(['universe_id', 'tick_created']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_scars');
    }
};
