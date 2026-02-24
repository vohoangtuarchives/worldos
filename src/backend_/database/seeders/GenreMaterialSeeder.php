<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Tuzy\Application\Genre\Genres\WuxiaGenre;
use Tuzy\Application\Genre\Genres\XianxiaGenre;
use Tuzy\Application\Genre\Genres\SystemGenre;
use Tuzy\Application\Genre\Genres\MagicalAcademyGenre;

class GenreMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWuxiaMaterials();
        $this->seedXianxiaMaterials();
        $this->seedSystemMaterials();
        $this->seedAcademyMaterials();
    }

    private function seedWuxiaMaterials(): void
    {
        $this->createMaterial('INTERNAL_ENERGY', 'Internal Energy', 'behavioral', 'transformative', [
            'productivity' => 0.2,
            'inequality' => 0.1,
            'myth_strength' => 0.3,
        ]);

        $this->createMaterial('SECT_REPUTATION', 'Sect Reputation', 'symbolic', 'stabilizing', [
            'legitimacy' => 0.4,
            'conflict_tension' => 0.2,
        ]);
    }

    private function seedXianxiaMaterials(): void
    {
        $this->createMaterial('SPIRIT_QI', 'Spirit Qi', 'behavioral', 'transformative', [
            'productivity' => 0.5,
            'inequality' => 0.8, // Cultivators hoard resources
            'mortality' => -0.2,
        ]);

        $this->createMaterial('DEMONIC_ENERGY', 'Demonic Energy', 'behavioral', 'destabilizing', [
            'corruption' => 0.6,
            'conflict_tension' => 0.5,
        ]);
        
        $this->createMaterial('HEAVENLY_TRIBULATION', 'Heavenly Tribulation', 'symbolic', 'destabilizing', [
            'mortality' => 0.4,
            'myth_strength' => 0.5,
        ]);
    }

    private function seedSystemMaterials(): void
    {
        $this->createMaterial('SYSTEM_OMNIPOTENCE', 'The System', 'structural', 'stabilizing', [
            'centralization' => 1.0,
            'inequality' => 0.5,
        ]);

        $this->createMaterial('DUNGEON_CORE', 'Dungeon Core', 'structural', 'destabilizing', [
            'threat' => 0.8,
            'resource_concentration' => 0.6,
        ]);
    }

    private function seedAcademyMaterials(): void
    {
        $this->createMaterial('MANA_LEYLINES', 'Mana Leylines', 'structural', 'transformative', [
            'productivity' => 0.3,
            'specialization' => 0.4,
        ]);

        $this->createMaterial('BLOODLINE_PURITY', 'Bloodline Purity', 'symbolic', 'stabilizing', [
            'inequality' => 0.7,
            'identity_rigidity' => 0.8,
        ]);
    }

    private function createMaterial(string $code, string $name, string $ontology, string $function, array $outputs): void
    {
        if (DB::table('materials')->where('code', $code)->exists()) {
            return;
        }

        DB::table('materials')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'code' => $code,
            // 'name' column does not exist
            'ontology' => $ontology,
            'function' => $function,
            'default_lifecycle' => 'active',
            'description' => $name . ' - Genre specific material.',
            'pressure_outputs' => json_encode($outputs),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
