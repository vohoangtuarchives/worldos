<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta_layer_states')) {
            return;
        }

        Schema::create('meta_layer_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Singleton usually has ID=1, no other unique constraints needed for now
            $table->float('chaos_pool')->default(0.0);
            $table->float('entropy_pressure')->default(0.0);
            $table->float('resource_flux')->default(0.0);
            $table->json('ideology_vector')->nullable(); // Stores array
            $table->json('myth_field')->nullable();      // Stores array
            $table->float('aggression_index')->default(0.0);
            $table->float('stability_index')->default(0.0);
            $table->float('mutation_bias')->default(0.0);
            $table->integer('current_era_index')->default(0);
            $table->timestamp('last_evolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_layer_states');
    }
};
