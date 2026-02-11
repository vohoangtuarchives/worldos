<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('world_event_ledger', function (Blueprint $table) {
            if (!Schema::hasColumn('world_event_ledger', 'description')) {
                $table->text('description')->nullable()->after('event_type');
            }
        });
    }

    public function down()
    {
        Schema::table('world_event_ledger', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
