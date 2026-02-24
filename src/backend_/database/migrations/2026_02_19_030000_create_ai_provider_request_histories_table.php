<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_request_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider', 100);
            $table->string('model', 191)->nullable();
            $table->string('endpoint', 512)->nullable();
            $table->longText('system_prompt')->nullable();
            $table->longText('user_prompt')->nullable();
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('status', 32);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_request_histories');
    }
};
