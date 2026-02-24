<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universe_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('universe_id');
            $table->integer('tick');
            $table->json('state_vector');
            $table->json('cascade_state')->nullable();
            $table->float('stability_metric')->nullable();
            $table->float('entropy');
            $table->json('metrics')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->onDelete('cascade');

            $table->index(['universe_id', 'tick']);
            $table->index('universe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_snapshots');
    }
};
