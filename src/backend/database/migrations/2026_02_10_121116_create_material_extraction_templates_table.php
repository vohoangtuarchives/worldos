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
        Schema::create('material_extraction_templates', function (Blueprint $table) {
            $table->id();
            $table->string('source_type'); // 'wikipedia', 'dataset', 'text'
            $table->string('source_url')->nullable();
            $table->json('raw_data')->comment('Original extracted data');
            $table->json('extracted_concepts')->comment('AI-extracted concepts');
            $table->json('material_template')->comment('Generated material definition');
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->json('validation_result')->nullable()->comment('Validation errors/warnings');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_extraction_templates');
    }
};
