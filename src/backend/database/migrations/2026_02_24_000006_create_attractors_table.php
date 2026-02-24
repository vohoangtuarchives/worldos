<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attractors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('universe_id');
            $table->string('type');
            $table->float('magnitude');
            $table->float('basin_depth');
            $table->float('activation_threshold')->default(0.5);
            $table->string('status')->default('dormant');
            $table->float('current_pull')->default(0.0);
            $table->timestamps();

            $table->foreign('universe_id')
                ->references('id')
                ->on('universes')
                ->cascadeOnDelete();

            $table->index(['universe_id', 'status']);
            $table->index(['universe_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractors');
    }
};
