<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_changes', function (Blueprint $table) {
            $table->id();
            $table->string('world_id');
            $table->string('instance_id');
            $table->enum('change_type', ['add', 'update', 'remove', 'transfer', 'degrade', 'upgrade', 'retire']);
            $table->json('old_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamp('occurred_at');

            // Indexes
            $table->index(['world_id', 'occurred_at']);
            $table->index(['instance_id', 'occurred_at']);
            $table->index(['change_type']);
            $table->index(['occurred_at']);
            
            // Foreign key constraints
            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_changes');
    }
};
