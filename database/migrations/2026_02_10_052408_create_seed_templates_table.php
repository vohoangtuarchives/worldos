<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seed_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // CONFLICT, DISCOVERY, TRAGEDY, etc.
            $table->string('dimension'); // personal, family, faction, city, world
            $table->integer('severity')->default(1);
            $table->json('metadata')->nullable(); // Custom fields for flexibility
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seed_templates');
    }
};
