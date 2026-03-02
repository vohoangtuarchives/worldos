<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialPressure;
use App\Models\MaterialMutation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    /**
     * Seed materials by origin (Vietnamese, European, Futuristic...).
     */
    public function run(?string $origin = 'generic'): void
    {
        $seeds = match ($origin) {
            'vietnamese' => $this->vietnamese(),
            'european' => $this->european(),
            'futuristic' => $this->futuristic(),
            'special' => $this->special(),
            default => array_merge($this->vietnamese(), $this->european(), $this->special()),
        };

        foreach ($seeds as $data) {
            $slug = Str::slug($data['name']);
            $material = Material::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'ontology' => $data['ontology'],
                    'lifecycle' => 'dormant',
                    'inputs' => $data['inputs'] ?? [],
                    'outputs' => $data['outputs'] ?? [],
                    'pressure_coefficients' => $data['pressure_coefficients'] ?? null,
                ]
            );

            if (! empty($data['pressures'])) {
                foreach ($data['pressures'] as $key => $coef) {
                    MaterialPressure::updateOrCreate(
                        ['material_id' => $material->id, 'vector_key' => $key],
                        ['coefficient' => $coef]
                    );
                }
            }
        }

        $this->seedMutations();
    }

    protected function seedMutations(): void
    {
        // Example mutation: Lúa nước -> Làng xã (if population > 0.6)
        $luaNuoc = Material::where('slug', 'nong-nghiep-lua-nuoc')->first();
        $langXa = Material::where('slug', 'lang-xa-tu-tri')->first();
        if ($luaNuoc && $langXa) {
            MaterialMutation::updateOrCreate(
                ['parent_material_id' => $luaNuoc->id, 'child_material_id' => $langXa->id],
                ['trigger_condition' => 'population > 0.6', 'context_constraint' => []]
            );
        }

        // Example mutation: Trống đồng -> Thờ cúng tổ tiên (if authority > 0.5)
        $trongDong = Material::where('slug', 'van-hoa-trong-dong')->first();
        $thoCung = Material::where('slug', 'tho-cung-to-tien')->first();
        if ($trongDong && $thoCung) {
            MaterialMutation::updateOrCreate(
                ['parent_material_id' => $trongDong->id, 'child_material_id' => $thoCung->id],
                ['trigger_condition' => 'authority > 0.5', 'context_constraint' => []]
            );
        }
    }

    protected function vietnamese(): array
    {
        return [
            [
                'name' => 'Nông nghiệp Lúa nước',
                'description' => 'Wet rice civilisation based on hydraulic management and communal labor.',
                'ontology' => Material::ONTOLOGY_PHYSICAL,
                'inputs' => ['water' => 0.3, 'entropy' => 0.5],
                'outputs' => ['order' => 1, 'growth' => 0.8, 'population' => 0.5],
                'pressures' => ['order' => 0.3, 'growth' => 0.2, 'entropy' => 0.05],
            ],
            [
                'name' => 'Thờ cúng Tổ tiên',
                'description' => 'Ancestor worship reinforcing lineage and social cohesion.',
                'ontology' => Material::ONTOLOGY_SYMBOLIC,
                'inputs' => ['order' => 0.2, 'tradition' => 0.1],
                'outputs' => ['order' => 0.5, 'stability' => 0.3],
                'pressures' => ['order' => 0.3, 'innovation' => -0.1, 'trauma' => 0.05],
            ],
            [
                'name' => 'Làng xã Tự trị',
                'description' => 'Village autonomy: "The King\'s law bows to village custom".',
                'ontology' => Material::ONTOLOGY_INSTITUTIONAL,
                'inputs' => ['population' => 0.2],
                'outputs' => ['order' => 0.4, 'resilience' => 0.6],
                'pressures' => ['order' => 0.2, 'centralization' => -0.3],
            ],
            [
                'name' => 'Văn hóa Trống đồng',
                'description' => 'Dong Son culture symbolizing power and community.',
                'ontology' => Material::ONTOLOGY_SYMBOLIC,
                'inputs' => ['bronze' => 0.5, 'craftsmanship' => 0.4],
                'outputs' => ['culture' => 0.7, 'authority' => 0.4],
                'pressures' => ['culture' => 0.3],
            ],
        ];
    }

    protected function european(): array
    {
        return [
            [
                'name' => 'Wheat Farming',
                'ontology' => Material::ONTOLOGY_PHYSICAL,
                'inputs' => ['entropy' => 0.6],
                'outputs' => ['growth' => 0.7, 'order' => 0.4],
                'pressures' => ['order' => 0.2, 'growth' => 0.25, 'entropy' => 0.05],
            ],
            [
                'name' => 'Feudal Hierarchy',
                'ontology' => Material::ONTOLOGY_INSTITUTIONAL,
                'outputs' => ['order' => 0.6],
                'pressures' => ['order' => 0.4, 'innovation' => -0.15],
            ],
            [
                'name' => 'Monotheism',
                'ontology' => Material::ONTOLOGY_SYMBOLIC,
                'pressures' => ['order' => 0.25, 'innovation' => -0.05],
            ],
        ];
    }

    protected function futuristic(): array
    {
        return [
            [
                'name' => 'Digital Tech',
                'ontology' => Material::ONTOLOGY_PHYSICAL,
                'outputs' => ['innovation' => 1, 'entropy' => 0.3, 'order' => 0.2],
                'pressures' => ['innovation' => 0.4, 'entropy' => 0.2, 'order' => 0.1],
            ],
        ];
    }

    protected function special(): array
    {
        return [
            [
                'name' => 'Hydro Essence',
                'description' => 'Primordial essence of water, bringing both life and overwhelming cataclysm.',
                'ontology' => Material::ONTOLOGY_PHYSICAL,
                'inputs' => [],
                'outputs' => ['entropy' => 0.8, 'growth' => 0.4],
                'pressures' => ['entropy' => 0.6, 'trauma' => 0.4, 'order' => -0.2],
            ],
            [
                'name' => 'Aether Shard',
                'description' => 'Crystallized magic or advanced energy, driving enlightenment and stability.',
                'ontology' => Material::ONTOLOGY_SYMBOLIC,
                'inputs' => [],
                'outputs' => ['innovation' => 0.9, 'order' => 0.7],
                'pressures' => ['innovation' => 0.5, 'order' => 0.4, 'growth' => 0.3],
            ],
            [
                'name' => 'Void Dust',
                'description' => 'Residual energy from the collapse, inducing fear and decay.',
                'ontology' => Material::ONTOLOGY_SYMBOLIC,
                'inputs' => [],
                'outputs' => ['entropy' => 1.0, 'trauma' => 1.0],
                'pressures' => ['entropy' => 0.8, 'trauma' => 0.6, 'order' => -0.5, 'growth' => -0.3],
            ],
        ];
    }
}
