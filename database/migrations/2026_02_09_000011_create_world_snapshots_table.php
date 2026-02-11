<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tick');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['world_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_snapshots');
    }
};
