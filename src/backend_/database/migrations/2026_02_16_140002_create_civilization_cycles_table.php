<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civilization_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('universe_id', 36);
            $table->unsignedInteger('cycle_number');
            $table->unsignedBigInteger('start_tick');
            $table->unsignedBigInteger('end_tick')->nullable();
            $table->string('collapse_reason', 64)->nullable();
            $table->timestamps();

            $table->index(['universe_id', 'cycle_number']);
        });

        if (Schema::hasTable('universes')) {
            Schema::table('civilization_cycles', function (Blueprint $table) {
                $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('civilization_cycles');
    }
};
