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
        Schema::table('world_myths', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->uuid('origin_universe_id')->nullable()->index()->after('strength');
            $table->string('genre_origin')->nullable()->after('origin_universe_id');
            $table->json('affected_materials')->nullable()->after('genre_origin');
            $table->timestamp('canonized_at')->nullable()->after('affected_materials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_myths', function (Blueprint $table) {
            $table->dropColumn([
                'description', 
                'origin_universe_id', 
                'genre_origin', 
                'affected_materials', 
                'canonized_at'
            ]);
        });
    }
};
