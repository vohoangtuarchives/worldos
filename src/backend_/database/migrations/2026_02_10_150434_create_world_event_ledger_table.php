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
        Schema::create('world_event_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->string('event_type'); // e.g., 'seal_crack', 'spirit_surge'
            $table->decimal('magnitude', 5, 2); // 0.00 to 1.00
            $table->decimal('permanence', 5, 2); // 0.00 to 1.00
            $table->string('visibility'); // 'secret', 'rumor', 'public'
            $table->integer('epoch');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['world_id', 'epoch']);
        });

        Schema::create('world_power_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('world_id')->unique()->constrained('worlds')->cascadeOnDelete();
            $table->string('current_stage');
            $table->decimal('accumulated_pressure', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_event_ledger');
        Schema::dropIfExists('world_power_stages');
    }
};
