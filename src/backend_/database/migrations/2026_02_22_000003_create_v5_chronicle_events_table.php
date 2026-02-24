<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WorldOS V5: Chronicle Events table.
 * Stores significant narrative events produced by the simulation engine each tick.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chronicle_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');

            $table->unsignedBigInteger('tick');
            $table->unsignedBigInteger('seed');

            $table->string('type', 64);       // EventType enum value
            $table->string('title');
            $table->string('severity', 16);   // low, medium, high, critical

            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            // Relations & Indexes
            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->onDelete('cascade');

            $table->index(['universe_id', 'tick']);
            $table->index(['universe_id', 'type']);
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronicle_events');
    }
};
