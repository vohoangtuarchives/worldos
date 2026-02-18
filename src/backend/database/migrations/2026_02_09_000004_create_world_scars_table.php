<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_scars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->foreignUuid('source_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->unsignedInteger('weight')->default(1);
            $table->timestamps();

            $table->index('world_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_scars');
    }
};
