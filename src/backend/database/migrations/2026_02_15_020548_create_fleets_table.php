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
        Schema::create('fleets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('universe_id'); // Match universes.id type (string)
            
            $table->unsignedBigInteger('faction_id')->nullable();
            $table->string('commander_id')->nullable(); // Legendary Agent ID (stored in JSON)
            
            $table->string('name');
            $table->string('type')->default('PATROL'); // PATROL, ARMADA, COLONY_SHIP
            $table->float('strength')->default(100.0);
            
            // Navigation
            $table->json('coordinates')->nullable(); // {x, y, z}
            $table->string('status')->default('IDLE'); // IDLE, MOVING, ENGAGING, DEFEATED
            $table->string('destination_universe_id')->nullable();
            
            $table->timestamps();

            $table->foreign('universe_id')->references('id')->on('universes')->onDelete('cascade');
            // Faction foreign key might need check if table exists and types match, skipping strict constraint for now to avoid order issues or use loose coupling
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
