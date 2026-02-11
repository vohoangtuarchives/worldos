<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observer_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observer_id')->constrained()->cascadeOnDelete();
            $table->string('version'); // e.g., 'v1', 'draft', 'final'
            $table->json('rules');     // Rules for interpretation (tone, filters)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observer_versions');
    }
};
