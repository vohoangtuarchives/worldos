<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wfr_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version')->unique(); // 1.0.0, 1.1.0, 2.0.0
            $table->text('changelog');
            $table->timestamp('released_at');
            $table->boolean('is_stable')->default(true);
            $table->timestamps();
            
            $table->index('version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfr_versions');
    }
};
