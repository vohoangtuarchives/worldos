<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('type'); // REJECTION_RATE, ECONOMY, FORK_EXPLOSION
            $table->string('severity'); // WARNING, CRITICAL
            $table->text('message');
            $table->json('details')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_alerts');
    }
};
