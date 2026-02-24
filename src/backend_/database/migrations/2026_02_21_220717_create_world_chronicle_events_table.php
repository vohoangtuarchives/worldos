<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('world_chronicle_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->integer('year');
            $table->string('type');
            $table->string('title');
            $table->text('description');
            $table->string('severity')->default('MEDIUM');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['world_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_chronicle_events');
    }
};
