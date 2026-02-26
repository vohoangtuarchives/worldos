<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * universe_myth_fields — Bảng lưu Myth (AXIOM 9, WorldOS 1.0.1 + Appendix_03).
 * m(t+1) = α*m(t) + F(events), α ∈ (0.95, 1) — phân rã rất chậm.
 * Lifecycle: dormant → active → decaying → dead
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universe_myth_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id')->index();
            $table->string('myth_id', 64)->unique();          // UUID hoặc slug
            $table->string('name', 255);                      // Tên huyền thoại
            $table->float('strength')->default(0.0);          // Cường độ ∈ [0,1]
            $table->string('status', 20)->default('dormant'); // dormant|active|decaying|dead
            $table->bigInteger('tick_created')->unsigned()->default(0);
            $table->bigInteger('tick_last_evolved')->unsigned()->default(0);
            $table->json('bias_vector')->nullable();           // Cache Bias Vector G(m)
            $table->timestamps();

            $table->index(['universe_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_myth_fields');
    }
};
