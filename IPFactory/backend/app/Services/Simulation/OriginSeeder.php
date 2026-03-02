<?php

namespace App\Services\Simulation;

use App\Models\Universe;
use App\Models\Material;
use App\Models\MaterialInstance;

class OriginSeeder
{
    /**
     * Tiêm DNA di sản vào vũ trụ dựa trên Origin của World.
     */
    public function seed(Universe $universe): void
    {
        $origin = $universe->world->origin;

        switch (strtolower($origin)) {
            case 'vietnamese':
                $this->seedVietnameseHeritage($universe);
                break;
            case 'western':
                $this->seedWesternHeritage($universe);
                break;
            case 'futuristic':
                $this->seedFuturisticHeritage($universe);
                break;
            default:
                // Mặc định không làm gì hoặc tiêm các giá trị cơ bản
                break;
        }
    }

    protected function seedVietnameseHeritage(Universe $universe): void
    {
        // 1. Tạo các Material đặc trưng
        $materials = [
            [
                'slug' => 'wet_rice_culture',
                'name' => 'Văn hóa Lúa nước',
                'ontology' => 'institutional',
                'description' => 'Nền tảng của sự ổn định và cộng đồng làng xã.',
                'pressure' => ['order' => 0.2, 'growth' => 0.1, 'entropy' => 0.05]
            ],
            [
                'slug' => 'ancestral_worship',
                'name' => 'Thờ cúng Tổ tiên',
                'ontology' => 'symbolic',
                'description' => 'Sợi dây liên kết tâm linh xuyên thế hệ.',
                'pressure' => ['order' => 0.3, 'innovation' => -0.1, 'stability' => 0.1]
            ],
            [
                'slug' => 'village_autonomy',
                'name' => 'Phép vua thua lệ làng',
                'ontology' => 'institutional',
                'description' => 'Tính tự quản cao của các đơn vị hành chính nhỏ.',
                'pressure' => ['order' => -0.1, 'stability' => 0.2, 'resistance' => 0.1]
            ]
        ];

        foreach ($materials as $m) {
            $model = Material::firstOrCreate(
                ['slug' => $m['slug']],
                [
                    'name' => $m['name'],
                    'ontology' => $m['ontology'],
                    'description' => $m['description'],
                    'pressure_coefficients' => $m['pressure']
                ]
            );

            MaterialInstance::create([
                'universe_id' => $universe->id,
                'material_id' => $model->id,
                'lifecycle' => 'active',
                'context' => [
                    'quantity' => 100,
                    'location' => ['x' => 0, 'y' => 0, 'z' => 0],
                    'origin' => 'Vietnamese'
                ]
            ]);
        }

        // 2. Ghi nhận vào Chronicle
        \App\Models\Chronicle::create([
            'universe_id' => $universe->id,
            'from_tick' => 0,
            'to_tick' => 0,
            'type' => 'myth',
            'content' => 'Hạt giống của văn minh Lạc Việt đã được gieo xuống, mang theo hơi thở của đất và hồn của tổ tiên.'
        ]);
    }

    protected function seedWesternHeritage(Universe $universe): void
    {
        // Placeholder for Western Heritage
    }

    protected function seedFuturisticHeritage(Universe $universe): void
    {
        // Placeholder for Futuristic Heritage
    }
}
