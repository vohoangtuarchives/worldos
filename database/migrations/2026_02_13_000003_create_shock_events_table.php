<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shock_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // UUID
            $table->foreignId('world_id')->constrained()->onDelete('cascade');
            $table->integer('world_tick')->default(0);
            $table->string('event_type'); // plague, civil_war, magic_collapse, etc.
            $table->float('severity'); // 0.0 to 1.0
            $table->string('affected_region');
            $table->float('entropy_delta');
            $table->json('risk_modifiers')->nullable(); // Risk factor changes
            $table->json('metadata')->nullable(); // Event-specific data
            $table->json('affected_characters')->nullable(); // Character IDs affected
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable(); // When event effects end
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['world_id', 'world_tick']);
            $table->index('event_type');
            $table->index(['is_active', 'occurred_at']);
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shock_events');
    }
};
