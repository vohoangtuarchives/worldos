<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intelligence_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id')->index();
            $table->string('type'); // intelligence_type enum
            $table->string('source_id');
            $table->string('source_type'); // intelligence_source_type enum
            $table->float('source_reliability');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamp('timestamp');
            $table->float('accuracy');
            $table->integer('age'); // age in hours
            $table->string('urgency')->default('medium'); // high, medium, low
            $table->float('reliability');
            $table->timestamps();
            
            // Indexes
            $table->index(['world_id', 'type']);
            $table->index(['world_id', 'urgency']);
            $table->index(['world_id', 'source_id']);
            $table->index(['world_id', 'timestamp']);
            $table->index(['type', 'urgency']);
            $table->index(['source_type', 'reliability']);
            $table->index(['accuracy']);
            $table->index(['age']);
            $table->index(['timestamp']);
            
            // Foreign key constraints
            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intelligence_reports');
    }
};
