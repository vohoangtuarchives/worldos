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
        Schema::create('saga_entropy_ledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('saga_id')->constrained('sagas');
            $table->foreignUuid('world_id')->nullable()->constrained('worlds')->nullOnDelete(); // Nullable for saga-wide events
            $table->string('source_type'); // e.g. 'GATE_LEAK', 'DECAY', 'COLLAPSE'
            $table->float('delta_entropy');
            $table->unsignedBigInteger('tick');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['saga_id', 'tick']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saga_entropy_ledgers');
    }
};
