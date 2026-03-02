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
        Schema::table('kernel_experiment_snapshots', function (Blueprint $table) {
            $table->json('zone_topology_json')->nullable()->after('structure_params');
            $table->double('global_entropy')->default(0.0)->after('zone_topology_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kernel_experiment_snapshots', function (Blueprint $table) {
            $table->dropColumn(['zone_topology_json', 'global_entropy']);
        });
    }
};
