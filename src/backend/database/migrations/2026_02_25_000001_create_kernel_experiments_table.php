<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kernel_experiments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kernel_version', 20);
            $table->string('commit_hash', 64)->nullable();

            // State dimensions
            $table->unsignedSmallInteger('n_dimension');
            $table->unsignedSmallInteger('n_regions')->default(1);

            // Kernel parameters (immutable after experiment starts)
            $table->double('alpha');
            $table->double('beta');
            $table->double('lambda');
            $table->double('eta');
            $table->double('gamma_cap');
            $table->double('delta_target');

            // Results (written at experiment end)
            $table->double('spectral_radius')->nullable();
            $table->double('margin')->nullable();
            $table->double('max_norm')->nullable();
            $table->double('gershgorin_max_bound')->nullable();
            $table->string('classification', 20)->nullable(); // convergent|saturated|near_boundary|rejected

            // Performance metrics
            $table->unsignedInteger('tick_count')->nullable();
            $table->double('avg_time_per_tick_ms')->nullable();
            $table->double('max_time_per_tick_ms')->nullable();
            $table->unsignedInteger('memory_peak_mb')->nullable();
            $table->unsignedInteger('total_runtime_ms')->nullable();
            $table->unsignedSmallInteger('stability_violations')->default(0);

            // Initial conditions
            $table->string('init_method', 20)->default('zero'); // zero|random_bounded|structured
            $table->unsignedBigInteger('random_seed')->nullable();
            $table->string('x0_hash', 64)->nullable();

            // Runtime config
            $table->string('precision_mode', 20)->default('float64'); // float64|bcmath|fixed
            $table->string('hardware_spec')->nullable();

            // Hash chain
            $table->string('final_snapshot_hash', 64)->nullable();

            // Status
            $table->string('status', 20)->default('running'); // running|completed|rejected|aborted
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kernel_experiments');
    }
};
