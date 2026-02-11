<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('timeline_node_id')->constrained('timeline_nodes')->cascadeOnDelete();
            $table->text('goal');
            $table->float('tension_target')->default(0.0);
            $table->string('status')->default('active'); // active, resolved, failed
            $table->json('state')->nullable(); // Arbitrary scene state
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
