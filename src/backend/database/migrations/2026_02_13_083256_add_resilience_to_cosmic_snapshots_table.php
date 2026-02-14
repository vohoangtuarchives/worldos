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
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->float('civ_resilience')->default(1.0)->after('civ_resonance_accumulator')
                ->comment('System fatigue/resilience (0.0-1.0). Decays with entropy.');
        });
    }

    public function down(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->dropColumn('civ_resilience');
        });
    }
};
