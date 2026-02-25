<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kernel_experiment_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('experiment_id');
            $table->unsignedInteger('tick');
            
            // Time-series data derived from the latent state
            $table->double('state_norm'); // ||x(t)||
            $table->double('ratio_r');    // ||x(t+1)|| / ||x(t)||
            
            // Real-time tracking of constraint margins
            $table->double('gershgorin_bound')->nullable();
            $table->unsignedSmallInteger('violations_count')->default(0);

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('experiment_id')
                  ->references('id')
                  ->on('kernel_experiments')
                  ->onDelete('cascade');

            $table->unique(['experiment_id', 'tick']);
            // Crucial index for ML time-series queries
            $table->index(['experiment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kernel_experiment_metrics');
    }
};
