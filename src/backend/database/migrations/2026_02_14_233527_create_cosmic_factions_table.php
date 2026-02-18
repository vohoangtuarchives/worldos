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
        Schema::create('cosmic_factions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ideology');
            $table->string('color')->default('#ffffff');
            $table->json('stats')->nullable(); // Buffs and collective metrics
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cosmic_factions');
    }
};
