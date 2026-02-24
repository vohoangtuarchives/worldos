<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_feature_agent_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('feature_key', 120)->unique();
            $table->string('agent_name', 191);
            $table->string('provider', 100)->default('openai');
            $table->string('model', 191)->nullable();
            $table->longText('system_prompt')->nullable();
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::table('ai_provider_request_histories', function (Blueprint $table) {
            $table->string('feature_key', 120)->nullable()->after('endpoint')->index();
            $table->string('agent_name', 191)->nullable()->after('feature_key');
        });
    }

    public function down(): void
    {
        Schema::table('ai_provider_request_histories', function (Blueprint $table) {
            $table->dropColumn(['feature_key', 'agent_name']);
        });

        Schema::dropIfExists('ai_feature_agent_configs');
    }
};
