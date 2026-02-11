<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('type'); // semantic, episodic
            $table->text('content');
            $table->string('visibility'); // public, private, secret
            $table->float('confidence')->default(1.0);
            $table->text('embedding')->nullable(); // Placeholder for vector
            
            // Optional: link to timeline node or specific event
            //$table->uuid('timeline_node_id')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_memories');
    }
};
