<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->timestamp('last_active_at')->useCurrent();
            $table->json('meta')->nullable(); // User Agent, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_sessions');
    }
};
