<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_world_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('type'); // myth_analysis, scar_cluster, etc.
            $table->json('content');
            $table->text('suggestion')->nullable();
            $table->timestamps();

            $table->index('world_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_world_reports');
    }
};
