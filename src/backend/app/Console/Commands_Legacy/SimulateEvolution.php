<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateEvolution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:evolution {world_id=1}';
    protected $description = 'Simulate the 3-phase world power evolution process';

    public function handle()
    {
        $worldId = $this->argument('world_id');
        $pressureService = app(\Tuzy\Application\Power\Services\WorldPressureService::class);
        $engine = app(\Tuzy\Domain\Power\StageTransitionEngine::class);

        $this->info("=== Khởi động mô phỏng tiến hoá thế giới (World: {$worldId}) ===");

        // 1. Check current state
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        if (!$state) {
            $this->error("Không tìm thấy trạng thái thế giới. Hãy chạy di dân (migration) hoặc tạo dữ liệu mẫu.");
            return;
        }

        $this->comment("Trạng thái hiện tại: Stage [{$state->current_stage}] | Phase [{$state->transition_phase}] | Áp lực [{$state->accumulated_pressure}]");

        // 2. Add structural pressure via Ledger
        $this->info("\n[1] Thêm biến cố chấn động (Seal Crack) vào Ledger...");
        DB::table('world_event_ledger')->insert([
            'world_id' => $worldId,
            'event_type' => 'seal_crack',
            'magnitude' => 0.8,
            'permanence' => 1.0,
            'visibility' => 'rumor',
            'epoch' => 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Sync Pressure & Check Transition
        $this->info("[2] Đồng bộ áp lực và kiểm tra tiến hoá...");
        $pressureService->checkTransition($worldId);
        
        $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
        $this->warn("GIAI ĐOẠN: {$state->transition_phase} (Vết nứt không gian bắt đầu rò rỉ Linh Khí)");
        $this->line("Dự kiến tiến tới: {$state->target_stage}");

        // 4. Trigger Moment
        if ($this->confirm('Kích hoạt biến cố phá vỡ trần thực tại (Trigger Moment)?')) {
            $engine->triggerMoment($worldId);
            $this->error("GIAI ĐOẠN: MOMENT (Trần thực tại sụp đổ! Luật vật lý đang bị viết lại)");
        }

        // 5. Post-Transition
        if ($this->confirm('Bắt đầu giai đoạn ổn định (Start Post-Transition)?')) {
            $engine->startStabilization($worldId);
            $this->warn("GIAI ĐOẠN: POST (Thế giới đang làm quen với trật tự mới. Dư chấn khắp nơi)");
        }

        // 6. Complete
        if ($this->confirm('Hoàn tất tiến hoá (Complete Transition)?')) {
            $engine->completeTransition($worldId);
            $state = DB::table('world_power_stages')->where('world_id', $worldId)->first();
            $this->info("CHÚC MỪNG! Thế giới đã tiến tới Stage: [{$state->current_stage}]. Trạng thái: STABLE.");
        }
    }
}
