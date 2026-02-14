<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_scars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_event_id')->constrained('world_events');
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
