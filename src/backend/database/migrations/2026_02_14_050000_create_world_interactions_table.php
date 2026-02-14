<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('world_a_id');
            $table->uuid('world_b_id');
            $table->string('interaction_type');
            $table->decimal('strength', 5, 3); // 0.000 to 99.999
            $table->integer('active_from_tick');
            $table->integer('active_to_tick')->nullable();
            $table->json('metadata')->nullable(); // Additional interaction data
            
            $table->timestamps();
            
            $table->index(['world_a_id', 'world_b_id']);
            $table->index('interaction_type');
            $table->index('strength');
            $table->index(['active_from_tick', 'active_to_tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_interactions');
    }
};
