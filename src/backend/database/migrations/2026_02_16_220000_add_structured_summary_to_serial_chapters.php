<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('serial_chapters')) {
            return;
        }
        if (Schema::hasColumn('serial_chapters', 'structured_summary')) {
            return;
        }
        Schema::table('serial_chapters', function (Blueprint $table) {
            $table->json('structured_summary')->nullable()->after('summary');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('serial_chapters', 'structured_summary')) {
            Schema::table('serial_chapters', function (Blueprint $table) {
                $table->dropColumn('structured_summary');
            });
        }
    }
};
