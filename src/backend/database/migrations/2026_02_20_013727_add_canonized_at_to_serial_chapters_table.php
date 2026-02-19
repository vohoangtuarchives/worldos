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
        Schema::table('serial_chapters', function (Blueprint $table) {
            $table->timestamp('canonized_at')->nullable()->after('consistency_notes');
            $table->string('impact_status')->default('pending')->after('canonized_at'); // pending, processed, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_chapters', function (Blueprint $table) {
            $table->dropColumn(['canonized_at', 'impact_status']);
        });
    }
};
