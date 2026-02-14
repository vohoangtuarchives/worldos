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
        Schema::create('reader_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id');
            $table->integer('epoch');
            $table->string('interaction_type', 50); // 'choice', 'reaction'
            $table->string('choice_id', 100)->nullable();
            $table->string('option_id', 100)->nullable();
            $table->string('reaction_type', 50)->nullable(); // 'support', 'oppose', 'sadness', 'anger', 'hope'
            $table->unsignedBigInteger('reader_id')->nullable(); // User ID if authenticated
            $table->string('reader_session', 100)->nullable(); // Session ID for anonymous
            $table->timestamps();

            $table->index(['world_id', 'epoch']);
            $table->index(['choice_id']);
        });

        Schema::create('choice_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('world_id');
            $table->integer('epoch');
            $table->string('choice_id', 100);
            $table->integer('total_votes');
            $table->string('winning_option', 100)->nullable();
            $table->json('vote_percentages');
            $table->json('applied_delta');
            $table->timestamps();

            $table->index(['world_id', 'epoch']);
            $table->unique(['world_id', 'epoch', 'choice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choice_results');
        Schema::dropIfExists('reader_interactions');
    }
};
