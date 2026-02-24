<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_faction', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('universe_id', 36);
            $table->foreignUuid('faction_id')->constrained('cosmic_factions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'universe_id']);
            $table->index('universe_id');
        });

        if (Schema::hasTable('universes')) {
            Schema::table('player_faction', function (Blueprint $table) {
                $table->foreign('universe_id')->references('id')->on('universes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('player_faction');
    }
};
