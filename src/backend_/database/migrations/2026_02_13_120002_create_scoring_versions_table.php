<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version', 50)->unique(); // 'v1.0', 'v2.0'
            
            // Weight configuration for impact score
            $table->json('dimension_weights'); 
            // { 'military': 1.2, 'governance': 1.1, ... }
            
            // Event → Dimension mapping matrix
            $table->json('event_dimension_map');
            // { 'battle': {'military': 1.0, 'territory': 0.3}, ... }
            
            // Config parameters
            $table->json('config');
            // { 'normalization_constant': 5.0, 'time_decay_lambda': 0.0003 }
            
            $table->boolean('is_active')->default(false)->index();
            $table->text('changelog')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_versions');
    }
};
