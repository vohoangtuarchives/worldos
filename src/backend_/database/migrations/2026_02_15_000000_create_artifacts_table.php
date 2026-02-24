<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifacts', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->string('name');
            $blueprint->text('description')->nullable();
            $blueprint->string('origin_universe_id')->nullable();
            $blueprint->string('owner_faction_id')->nullable();
            $blueprint->json('power_stats'); // e.g. { order_boost: 0.1, entropy_reduction: 0.05 }
            $blueprint->string('rarity'); // COMMON, RARE, LEGENDARY, COSMIC
            $blueprint->string('status')->default('IN_Bazaar'); // IN_Bazaar, INFUSED, DESTROYED
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};
