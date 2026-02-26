<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * universe_scar_memories — Bảng lưu Scar (AXIOM 10, WorldOS 1.0.1).
 * Append-only: không xóa, không update magnitude trực tiếp — chỉ insert record mới.
 * energy_cap_ratio = 1 - β * magnitude (tự động cập nhật khi đọc)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universe_scar_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id')->index(); // FK tới universes
            $table->float('magnitude')->default(0.0);          // ||S|| ∈ [0,1]
            $table->float('energy_cap_ratio')->default(1.0);   // 1 - β*||S||
            $table->integer('collapse_count')->default(0);     // Số lần sụp đổ
            $table->float('ptsd_factor')->default(1.0);        // exp(-γ*||S||)
            $table->boolean('is_critical')->default(false);    // magnitude ≥ 0.85
            $table->bigInteger('recorded_at_tick')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universe_scar_memories');
    }
};
