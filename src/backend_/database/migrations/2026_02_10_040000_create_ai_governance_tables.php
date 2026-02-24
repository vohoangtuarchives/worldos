<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('prompt_hash')->index(); // For deduplication/search
            $table->text('system_prompt');
            $table->text('user_prompt');
            $table->longText('response_content')->nullable();
            $table->string('status'); // 'ACCEPTED', 'REJECTED', 'FAILED'
            $table->json('violations')->nullable(); // If rejected
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
        });

        Schema::create('ai_extracted_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('generation_id')->constrained('ai_generations')->cascadeOnDelete();
            $table->string('claim_type');
            $table->integer('magnitude')->nullable();
            $table->string('subject')->nullable();
            $table->boolean('is_valid');
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extracted_claims');
        Schema::dropIfExists('ai_generations');
    }
};
