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
        Schema::table('worlds', function (Blueprint $table) {
            $table->double('current_time', 15, 4)->default(0)->after('tick'); // Using double for precision float (e.g., 50.0027)
            $table->json('calendar_system')->nullable()->after('current_time');
        });

        Schema::table('world_events', function (Blueprint $table) {
            $table->double('world_time', 15, 4)->default(0)->after('tick');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->dropColumn(['current_time', 'calendar_system']);
        });

        Schema::table('world_events', function (Blueprint $table) {
            $table->dropColumn('world_time');
        });
    }
};
