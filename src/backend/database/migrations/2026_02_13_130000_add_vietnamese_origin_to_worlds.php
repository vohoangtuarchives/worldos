<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            // Origin system fields
            $table->string('origin_type', 50)->default('cosmic')->after('preset');
            // Values: 'cosmic', 'vietnamese', 'norse', etc.
            
            $table->json('origin_metadata')->nullable()->after('origin_type');
            // Vietnamese: {progenitors, egg_count, mountain_sea_split_era, activated_heroes}
            // Cosmic: {formation_type, primordial_state}
            // Norse: {9_realms, world_tree_position}
            
            // Chaos/Primordial parameters
            $table->decimal('initial_entropy', 3, 2)->default(0.95)->after('origin_metadata');
            $table->decimal('initial_energy', 3, 2)->default(0.80)->after('initial_entropy');
            $table->decimal('initial_stability', 3, 2)->default(0.10)->after('initial_energy');
            
            // Current cosmic state
            $table->decimal('cosmic_energy', 3, 2)->default(0.80)->after('initial_stability');
            $table->decimal('cosmic_entropy', 3, 2)->default(0.95)->after('cosmic_energy');
            $table->decimal('cosmic_stability', 3, 2)->default(0.10)->after('cosmic_entropy');
            
            // Yggdrasil positioning
            $table->string('yggdrasil_realm', 50)->default('TRUNK')->after('cosmic_stability');
            // Values: 'UPPER_LEAVES', 'TRUNK', 'ROOTS'
            
            // Era system (50 years per era)
            $table->integer('current_era')->default(0)->after('yggdrasil_realm');
            
            // Bifurcation tracking (parent_id already exists)
            $table->integer('bifurcation_era')->nullable()->after('current_era');
            $table->string('bifurcation_type', 100)->nullable()->after('bifurcation_era');
            $table->string('bifurcation_trigger', 255)->nullable()->after('bifurcation_type');
            
            // Indexes
            $table->index('origin_type');
            $table->index('current_era');
        });
    }

    public function down(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->dropColumn([
                'origin_type', 'origin_metadata',
                'initial_entropy', 'initial_energy', 'initial_stability',
                'cosmic_energy', 'cosmic_entropy', 'cosmic_stability',
                'yggdrasil_realm', 'current_era',
                'bifurcation_era', 'bifurcation_type', 'bifurcation_trigger',
            ]);
        });
    }
};
