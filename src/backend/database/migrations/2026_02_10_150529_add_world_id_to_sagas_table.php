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
        Schema::table('sagas', function (Blueprint $table) {
            $table->unsignedBigInteger('world_id')->nullable()->after('id');
            $table->string('anchor_stage')->default('mundane');
            $table->integer('anchor_epoch')->default(0);
            $table->string('power_scope')->default('local');

            $table->foreign('world_id')->references('id')->on('worlds')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sagas', function (Blueprint $table) {
            $table->dropForeign(['world_id']);
            $table->dropColumn(['world_id', 'anchor_stage', 'anchor_epoch', 'power_scope']);
        });
    }
};
