
??php

namespace App\Domains\World\Services;

use App\Models\World;
use App\Models\WorldPowerProfile;
use Illuminate\Support\Collection;

class CollisionResolutionService
{
    public function resolve(World $source, World $target): array
    {
        $sourceProfile = $source-powerProfile ?? WorldPowerProfile::where('world_id', $source-id)->first();
        $targetProfile = $target-powerProfile ?? WorldPowerProfile::where('world_id', $target-id)->first();

        if (!$sourceProfile || !$targetProfile) {
            return [];
        }

        $events = [];

        $sourceSchema = config('power_schemas')[$sourceProfile->schema_key] ?? [];
        $targetSchema = config('power_schemas')[$targetProfile->schema_key] ?? [];

        $commonMaterials = $this-intersectMaterials($sourceProfile, $targetProfile);
        if ($commonMaterials-isNotEmpty()) {
            $events[] = [
                'type' => 'resource_trade',
                'severity' => 5,
                'description' => 'Hai thế giới trao đổi vật liệu hiếm: '. implode(', ', $commonMaterials-toArray()),
            ];
        }

        $powerConflict = $this-detectPowerConflict($sourceSchema, $targetSchema);
        if ($powerConflict) {
            $events[] = $powerConflict;
        }

        $culturalExchange = $this-detectCulturalExchange($sourceSchema, $targetSchema);
        if ($culturalExchange) {
            $events[] = $culturalExchange;
        }

        return $events;
    }

    private function intersectMaterials(WorldPowerProfile $a, WorldPowerProfile $b): Collection
    {
        return collect($a-material_affinities)
            -intersect($b-material_affinities);
    }

    private function detectPowerConflict(array $sourceSchema, array $targetSchema): ?array
    {
        $sourceSystem = $sourceSchema['power_system'] ?? 'NONE';
        $targetSystem = $targetSchema['power_system'] ?? 'NONE';

        if ($sourceSystem === $targetSystem) {
            return null;
        }

        return [
            'type' => 'power_conflict',
            'severity' => 7,
            'description' => "Hệ sức mạnh {$sourceSystem} va chạm với {$targetSystem}, gây ra biến động luật lệ.",
        ];
    }

    private function detectCulturalExchange(array $sourceSchema, array $targetSchema): ?array
    {
        $sourceTone = $sourceSchema['narrative']['tone'] ?? null;
        $targetTone = $targetSchema['narrative']['tone'] ?? null;

        if (!$sourceTone || !$targetTone || $sourceTone === $targetTone) {
            return null;
        }

        return [
            'type' => 'cultural_exchange',
            'severity' => 4,
            'description' => "Phong cách {$sourceTone} ảnh hưởng {$targetTone}, tạo nên trào lưu mới.",
        ];
    }
}
