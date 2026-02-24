<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('characters')->cascadeOnDelete();
            $table->text('description');
            $table->integer('priority'); // Higher is more important
            $table->string('status'); // active, completed, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_goals');
    }
};
