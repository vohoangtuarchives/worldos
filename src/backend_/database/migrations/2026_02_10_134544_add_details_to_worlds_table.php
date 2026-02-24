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
            if (!Schema::hasColumn('worlds', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('worlds', 'current_epoch')) {
                $table->integer('current_epoch')->default(0)->after('description');
            }
            if (!Schema::hasColumn('worlds', 'status')) {
                $table->string('status', 20)->default('active')->after('current_epoch');
            }
            if (!Schema::hasColumn('worlds', 'law_profile')) {
                $table->json('law_profile')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->dropColumn(['description', 'current_epoch', 'status', 'law_profile']);
        });
    }
};
