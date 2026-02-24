<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 4: ConsistencyValidator flags chapters that need human review.
     */
    public function up(): void
    {
        Schema::table('serial_chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('serial_chapters', 'needs_review')) {
                $table->boolean('needs_review')->default(false)->after('structured_summary');
            }
            if (!Schema::hasColumn('serial_chapters', 'consistency_notes')) {
                $table->json('consistency_notes')->nullable()->after('needs_review');
            }
        });
    }

    public function down(): void
    {
        Schema::table('serial_chapters', function (Blueprint $table) {
            $table->dropColumn(['needs_review', 'consistency_notes']);
        });
    }
};
