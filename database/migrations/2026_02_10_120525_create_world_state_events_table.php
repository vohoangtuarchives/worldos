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
        Schema::create('world_state_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->onDelete('cascade');
            $table->integer('epoch')->comment('Tick/year number');
            $table->json('deltas')->comment('State changes this tick');
            $table->json('origins')->comment('Material codes that caused deltas');
            $table->json('tick_results')->nullable()->comment('Full tick output from MaterialLawEngine');
            $table->timestamp('created_at');
            
            $table->index(['world_id', 'epoch']);
            $table->unique(['world_id', 'epoch']); // One event per epoch
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_state_events');
    }
};
