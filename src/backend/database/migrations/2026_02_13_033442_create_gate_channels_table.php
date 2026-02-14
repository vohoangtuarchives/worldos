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
        Schema::create('gate_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_world_id')->constrained('worlds');
            $table->foreignId('target_world_id')->constrained('worlds');
            $table->string('type')->default('WORMHOLE'); // WORMHOLE, LEAK, RIFT
            $table->float('stability')->default(1.0); // 0.0 - 1.0 (1.0 = Stable)
            $table->float('throughput')->default(0.0); // How much entropy flows per tick
            $table->boolean('is_active')->default(true);
            $table->jsonb('config')->nullable();
            $table->timestamps();

            $table->index(['source_world_id', 'target_world_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_channels');
    }
};
