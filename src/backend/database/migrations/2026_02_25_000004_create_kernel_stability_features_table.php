<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kernel_stability_features', function (Blueprint $table) {
            $table->uuid('experiment_id')->primary();
            
            // Structural definitions
            $table->unsignedInteger('dimension_n');
            
            // Evaluated features
            $table->double('gershgorin_max_bound')->nullable();
            $table->double('laplacian_trace')->nullable();
            $table->double('A_trace')->nullable();
            $table->double('spectral_gap_estimate')->nullable();
            $table->double('structure_entropy')->nullable();
            $table->double('estimated_beta_max')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('experiment_id')
                  ->references('id')
                  ->on('kernel_experiments')
                  ->onDelete('cascade');
            
            // Indices specifically targeted for ML feature extraction pipelines
            $table->index('gershgorin_max_bound');
            $table->index(['dimension_n', 'spectral_gap_estimate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kernel_stability_features');
    }
};
