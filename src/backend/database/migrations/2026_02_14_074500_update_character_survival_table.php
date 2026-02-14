<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_survival', function (Blueprint $table) {
            // Missing columns identified from Repository
            if (!Schema::hasColumn('character_survival', 'world_id')) {
                $table->string('world_id')->after('id')->index();
            }
            if (!Schema::hasColumn('character_survival', 'name')) {
                $table->string('name')->nullable()->after('world_id');
            }
            if (!Schema::hasColumn('character_survival', 'faction')) {
                $table->string('faction')->nullable()->after('name');
            }
            if (!Schema::hasColumn('character_survival', 'location')) {
                $table->string('location')->nullable()->after('faction');
            }
            if (!Schema::hasColumn('character_survival', 'age')) {
                $table->integer('age')->default(0)->after('is_alive');
            }
            if (!Schema::hasColumn('character_survival', 'cause_of_death')) {
                $table->string('cause_of_death')->nullable()->after('death_tick');
            }

            // Fix survival_probability naming/type
            if (Schema::hasColumn('character_survival', 'current_survival_probability')) {
                $table->renameColumn('current_survival_probability', 'survival_probability');
            } else if (!Schema::hasColumn('character_survival', 'survival_probability')) {
                 $table->float('survival_probability')->nullable();
            }

            // Fix narrative_weight: Rename JSON col (if exists) and add Float col
            if (Schema::hasColumn('character_survival', 'narrative_weight')) {
                // Rename existing JSON column to _data
                 $table->renameColumn('narrative_weight', 'narrative_weight_data');
            } else {
                 $table->json('narrative_weight_data')->nullable();
            }
        });

        // Add the float column for sorting/filtering
        Schema::table('character_survival', function (Blueprint $table) {
             if (!Schema::hasColumn('character_survival', 'narrative_weight')) {
                $table->float('narrative_weight')->default(0.0)->after('narrative_weight_data'); // Scalar score
             }
        });
    }

    public function down(): void
    {
        Schema::table('character_survival', function (Blueprint $table) {
            $table->dropColumn(['world_id', 'name', 'faction', 'location', 'age', 'cause_of_death']);
            $table->renameColumn('survival_probability', 'current_survival_probability');
            $table->dropColumn('narrative_weight'); // Drop the float
            $table->renameColumn('narrative_weight_data', 'narrative_weight'); // Revert name
        });
    }
};
