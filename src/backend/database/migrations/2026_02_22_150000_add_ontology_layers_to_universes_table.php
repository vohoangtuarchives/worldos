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
        Schema::table('universes', function (Blueprint $table) {
            // V6 Ontology Layers
            $table->json('culture_vector')->nullable()->after('current_state_vector');
            $table->json('ideology_vector')->nullable()->after('culture_vector');
            
            // Simulation Meta-data
            $table->decimal('influence_mass', 20, 4)->default(1.0)->after('stability_index');
            $table->integer('stability_duration')->default(0)->after('influence_mass');
            $table->string('lifecycle_state')->default('emerging')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropColumn([
                'culture_vector',
                'ideology_vector',
                'influence_mass',
                'stability_duration',
                'lifecycle_state'
            ]);
        });
    }
};
