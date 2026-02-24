<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Manually drop columns that may exist from failed migration attempts
        $columnsToCheck = [
            'origin_type', 'origin_metadata',
            'initial_entropy', 'initial_energy', 'initial_stability',
            'cosmic_energy', 'cosmic_entropy', 'cosmic_stability',
            'yggdrasil_realm', 'current_era',
            'bifurcation_era', 'bifurcation_type', 'bifurcation_trigger',
        ];
        
        $toDrop = [];
        foreach ($columnsToCheck as $column) {
            if (Schema::hasColumn('worlds', $column)) {
                $toDrop[] = $column;
            }
        }
        
        if (!empty($toDrop)) {
            Schema::table('worlds', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        // No rollback needed - this is a cleanup migration
    }
};
