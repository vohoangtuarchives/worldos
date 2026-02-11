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
        Schema::create('world_state_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->onDelete('cascade');
            $table->integer('epoch')->comment('Snapshot epoch');
            $table->json('core_state')->comment('CoreState component');
            $table->json('structural_state')->comment('StructuralState component');
            $table->json('symbolic_state')->comment('SymbolicState component');
            $table->json('memory_state')->comment('MemoryState component');
            $table->json('interaction_state')->comment('InteractionState component');
            $table->json('meta_state')->comment('MetaState component');
            $table->timestamp('created_at');
            
            $table->index(['world_id', 'epoch']);
            $table->unique(['world_id', 'epoch']); // One snapshot per epoch
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_state_snapshots');
    }
};
