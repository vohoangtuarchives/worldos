<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('narrative_projections')) {
            return;
        }
        Schema::create('narrative_projections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('universe_id', 36)->nullable();
            $table->unsignedBigInteger('tick')->default(0);
            $table->string('event_type', 64)->nullable();
            $table->string('event_id', 36)->nullable();
            $table->longText('text');
            $table->json('structured_summary')->nullable();
            $table->timestamps();
            $table->index(['universe_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_projections');
    }
};
