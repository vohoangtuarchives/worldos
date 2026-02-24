<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('instance_id')->unique(); // UUID
            $table->foreignUuid('world_id')->constrained('worlds')->cascadeOnDelete();
            $table->uuid('material_id'); // references materials.id (materials table created in a later migration)
            $table->float('strength_level'); // 0.0 to 10.0
            $table->float('durability'); // 0.0 to 100.0
            $table->float('purity'); // 0.0 to 1.0
            $table->string('location');
            $table->string('owner')->nullable();
            $table->enum('state', [
                'stable', 'worn', 'damaged', 'broken', 
                'unstable', 'corrupted', 'retired'
            ])->default('stable');
            $table->float('instability')->default(0.0); // 0.0 to 1.0
            $table->float('corruption')->default(0.0); // 0.0 to 1.0
            $table->json('metadata')->nullable(); // Additional properties
            $table->timestamp('created_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->string('retirement_reason')->nullable();

            // Indexes
            $table->index(['world_id', 'material_id']);
            $table->index(['world_id', 'state']);
            $table->index(['world_id', 'location']);
            $table->index(['world_id', 'owner']);
            $table->index(['material_id', 'state']);
            $table->index(['state', 'durability']);
            $table->index(['instability']);
            $table->index(['corruption']);
            $table->index(['retired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_materials');
    }
};
