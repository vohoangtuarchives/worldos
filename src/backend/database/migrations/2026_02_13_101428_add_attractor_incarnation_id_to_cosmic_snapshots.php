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
            $table->uuid('attractor_incarnation_id')->nullable()->after('attractor');
            $table->foreign('attractor_incarnation_id')->references('id')->on('attractor_incarnations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cosmic_snapshots', function (Blueprint $table) {
            $table->dropForeign(['attractor_incarnation_id']);
            $table->dropColumn('attractor_incarnation_id');
        });
    }
};
