<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_primitives', function (Blueprint $table) {
            $table->id();
            $table->string('domain'); // civilization, culture, economy, power, ontological
            $table->string('code')->unique(); // MONARCHY, HONOR_BASED, etc.
            $table->string('name'); // Human-readable name
            $table->text('description');
            $table->json('constraints')->nullable(); // What this enables/forbids
            $table->string('version')->default('1.0.0'); // WFR version
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['domain', 'version']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_primitives');
    }
};
