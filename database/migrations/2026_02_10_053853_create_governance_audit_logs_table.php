<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governance_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_type'); // FORK, KILL_SWITCH, LAW_CHANGE, ALERT_RESOLVED, etc.
            $table->string('operator'); // auth()->user()->email
            $table->json('metadata')->nullable(); // Action details
            $table->string('severity')->default('INFO'); // INFO, WARNING, CRITICAL
            $table->timestamps();
            
            $table->index(['world_id', 'created_at']);
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governance_audit_logs');
    }
};
