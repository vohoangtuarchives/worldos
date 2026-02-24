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
        Schema::create('universes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('age')->default(0);
            $table->json('state_vector');
            $table->json('parameters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universes');
    }
};
