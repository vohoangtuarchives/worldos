<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_clocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->unsignedBigInteger('current_tick')->default(0);
            $table->timestamps();

            $table->unique('world_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_clocks');
    }
};
