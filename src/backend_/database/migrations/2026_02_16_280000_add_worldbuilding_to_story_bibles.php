<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Worldbuilding có cấu trúc: power_system, factions, locations, timeline (rule engine / consistency).
     */
    public function up(): void
    {
        Schema::table('story_bibles', function (Blueprint $table) {
            if (!Schema::hasColumn('story_bibles', 'worldbuilding_rules')) {
                $table->json('worldbuilding_rules')->nullable()->after('style_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('story_bibles', function (Blueprint $table) {
            $table->dropColumn('worldbuilding_rules');
        });
    }
};
