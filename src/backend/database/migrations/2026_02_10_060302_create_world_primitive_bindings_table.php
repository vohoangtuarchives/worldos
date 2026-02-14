<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_primitive_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->foreignId('world_primitive_id')->constrained('world_primitives');
            $table->string('wfr_version'); // Locked to version at world creation
            $table->timestamps();
            
            $table->unique(['world_id', 'world_primitive_id'], 'world_primitive_unique');
            $table->index('wfr_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_primitive_bindings');
    }
};
