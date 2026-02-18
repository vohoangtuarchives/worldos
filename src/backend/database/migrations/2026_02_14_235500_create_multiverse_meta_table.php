<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multiverse_meta', function (Blueprint $table) {
            $table->id();
            $table->float('entropy_leak')->default(0.0);
            $table->float('shield_level')->default(0.0);
            $table->json('void_zones')->nullable(); // Store coordinates and radius of incursions
            $table->timestamps();
        });

        // Initialize with default record
        \DB::table('multiverse_meta')->insert([
            'entropy_leak' => 0.0,
            'shield_level' => 0.0,
            'void_zones' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('multiverse_meta');
    }
};
