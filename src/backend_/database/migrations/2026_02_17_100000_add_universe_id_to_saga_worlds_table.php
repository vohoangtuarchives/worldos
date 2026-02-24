<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saga runtime-first: each SagaWorld can reference the Universe being ticked.
     */
    public function up(): void
    {
        Schema::table('saga_worlds', function (Blueprint $table) {
            $table->string('universe_id')->nullable()->after('world_id');
            $table->foreign('universe_id')->references('id')->on('universes')->onDelete('set null');
            $table->index('universe_id');
        });
    }

    public function down(): void
    {
        Schema::table('saga_worlds', function (Blueprint $table) {
            $table->dropForeign(['universe_id']);
            $table->dropColumn('universe_id');
        });
    }
};
