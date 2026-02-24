<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cosmic_factions', function (Blueprint $table) {
            $table->string('status', 32)->default('ACTIVE');
            $table->foreignUuid('parent_faction_id')->nullable()->constrained('cosmic_factions')->nullOnDelete();
            $table->unsignedInteger('cycle_origin')->nullable();
            $table->unsignedInteger('cycles_survived')->default(0);
            $table->float('ideology_adaptability')->nullable();
            $table->float('resource_control')->nullable();
            $table->float('network_resilience')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cosmic_factions', function (Blueprint $table) {
            $table->dropForeign(['parent_faction_id']);
            $table->dropColumn([
                'status', 'parent_faction_id', 'cycle_origin', 'cycles_survived',
                'ideology_adaptability', 'resource_control', 'network_resilience',
            ]);
        });
    }
};
