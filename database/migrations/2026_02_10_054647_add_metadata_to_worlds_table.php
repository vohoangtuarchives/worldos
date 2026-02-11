<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name'); // XIANXIA, FANTASY, SCIFI, etc.
            $table->text('description')->nullable()->after('type'); // World lore/summary
            $table->json('config')->nullable()->after('law_profile'); // Additional metadata
            $table->json('tags')->nullable()->after('config'); // Categorization tags
        });
    }

    public function down(): void
    {
        Schema::table('worlds', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'config', 'tags']);
        });
    }
};
