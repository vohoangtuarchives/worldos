<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sagas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('preset_key')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sagas');
    }
};
