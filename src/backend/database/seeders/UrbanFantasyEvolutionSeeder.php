<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Tuzy\Domain\Power\ValueObject\PowerStage;

class UrbanFantasyEvolutionSeeder extends Seeder
{
    public function run()
    {
        $worldId = DB::table('worlds')->first()->id ?? 1;

        // 1. Initialize World Power Stage
        DB::table('world_power_stages')->updateOrInsert(
            ['world_id' => $worldId],
            [
                'current_stage' => 'mundane',
                'accumulated_pressure' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Seed Events for Chapter 1-30 (The "Hidden" Phase)
        // These events are low visibility, accumulating pressure slowly.
        
        $events = [
            [
                'world_id' => $worldId,
                'event_type' => 'spirit_leak',
                'description' => 'Một dòng linh khí nhỏ rò rỉ tại khu nhà kho bỏ hoang phía Nam thành phố.',
                'magnitude' => 0.1,
                'permanence' => 0.5, // Will fade if not reinforced
                'visibility' => 'secret', // Only sensitive individuals know
                'epoch' => 1
            ],
            [
                'world_id' => $worldId,
                'event_type' => 'awakening_incident',
                'description' => 'Học sinh trung học bộc phát sức mạnh thể chất bất thường trong giờ thể dục.',
                'magnitude' => 0.05,
                'permanence' => 1.0, // Identifying a person is permanent
                'visibility' => 'rumor', // School rumors
                'epoch' => 5
            ],
            [
                'world_id' => $worldId,
                'event_type' => 'sect_conflict',
                'description' => 'Giao tranh giữa hai nhóm hắc y nhân tại bến cảng. Không có thương vong dân sự.',
                'magnitude' => 0.15,
                'permanence' => 0.8,
                'visibility' => 'secret', // Cleaned up primarily
                'epoch' => 12
            ],
            [
                'world_id' => $worldId,
                'event_type' => 'artifact_discovery',
                'description' => 'Khai quật được cổ vật lạ tại công trường xây dựng tàu điện ngầm.',
                'magnitude' => 0.2,
                'permanence' => 1.0,
                'visibility' => 'public_masked', // Reported as "Archaeological Find"
                'epoch' => 20
            ],
             [
                'world_id' => $worldId,
                'event_type' => 'seal_crack_minor',
                'description' => 'Vết nứt không gian nhỏ xuất hiện trong giây lát trên bầu trời đêm.',
                'magnitude' => 0.25,
                'permanence' => 0.9,
                'visibility' => 'rumor', // "Did you see that light?"
                'epoch' => 28
            ]
        ];

        foreach ($events as $event) {
            DB::table('world_event_ledger')->insert(array_merge($event, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // Note: Total Pressure approx = 
        // 0.1*0.5 + 0.05*1.0 + 0.15*0.8 + 0.2*1.0 + 0.25*0.9 
        // = 0.05 + 0.05 + 0.12 + 0.2 + 0.225 = 0.645
        // This > 0.4 (Mundane threshold) so next checkTransition will trigger 'mortal_martial'.
    }
}
