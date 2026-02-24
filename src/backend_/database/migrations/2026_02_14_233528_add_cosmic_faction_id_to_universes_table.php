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
            $table->foreignUuid('cosmic_faction_id')->nullable()->after('id')->constrained('cosmic_factions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['cosmic_faction_id']);
            $table->dropColumn('cosmic_faction_id');
        });
    }
};
