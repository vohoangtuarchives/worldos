<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hero_id');
            $table->string('event_type', 50)->index(); 
            // 'battle', 'reform', 'writing_book', 'rebellion', 'founding_state', 
            // 'religion_founding', 'territorial_expansion', 'diplomacy', 'myth_event', 
            // 'legal_reform', 'education_system', 'economic_policy'
            
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->integer('year')->nullable();
            
            // Scoring parameters
            $table->integer('scale')->default(3); // 1 (local) - 5 (civilizational)
            $table->integer('duration_years')->default(1);
            $table->decimal('success', 3, 2)->default(1.0); // 0.0 (failed) - 1.0 (success)
            
            // Additional metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->foreign('hero_id')
                ->references('id')
                ->on('vietnamese_heroes')
                ->onDelete('cascade');
            
            $table->index(['hero_id', 'event_type']);
            $table->index(['event_type', 'scale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_events');
    }
};
