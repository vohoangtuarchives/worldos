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
        Schema::create('realm_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('realm_name');
            $table->string('period_name')->nullable();
            $table->string('influence_type'); // DOMINATION, TRADE, WAR, ALLIANCE
            $table->float('intensity')->default(0.5);
            $table->integer('start_era')->default(0);
            $table->integer('end_era')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realm_contacts');
    }
};
