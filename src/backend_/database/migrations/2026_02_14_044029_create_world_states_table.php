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
        if (!Schema::hasTable('world_states')) {
            Schema::create('world_states', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->json('power_axis');
                $table->json('resource_axis');
                $table->json('perception_axis');
                $table->json('author_intent');
                $table->string('structural_anchor');
                $table->decimal('resistance_factor', 3, 2)->default(0.15);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_states');
    }
};
