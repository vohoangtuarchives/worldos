<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worlds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('preset'); // martial, immortal, apocalypse, tech, myth
            $table->json('gene_vector'); // World genetic makeup
            $table->float('entropy')->default(0.0); // Current entropy level (0.0 to 1.0)
            $table->integer('current_tick')->default(0); // Current tick number
            $table->boolean('autonomous')->default(false); // Autonomous mode status
            $table->timestamp('last_tick_at')->nullable(); // Last tick timestamp
            $table->string('lifecycle_phase')->nullable(); // rising, peak, declining, collapsing
            $table->text('description')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['preset']);
            $table->index(['autonomous']);
            $table->index(['entropy']);
            $table->index(['current_tick']);
            $table->index(['last_tick_at']);
            $table->index(['lifecycle_phase']);
            $table->index(['archived']);
            $table->index(['created_at']);
            
            // Unique index for names
            $table->unique(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worlds');
    }
};
