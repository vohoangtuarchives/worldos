<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worlds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->json('law_vector');
            $table->string('preset_key', 100);
            $table->string('origin_type', 50)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('preset_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worlds');
    }
};
