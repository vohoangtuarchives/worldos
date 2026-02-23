
??php

namespace App\Domains\Material\Services;

use App\Domains\Material\Material;
use App\Domains\Material\MaterialInstance;
use WorldOS\Legacy\Domain\Material\Enums\MaterialOntology;
use WorldOS\Legacy\Domain\Material\Enums\MaterialFunction;
use App\Models\World;
use App\Domains\WorldManagement\Services\AIGovernanceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MaterialIngestionService
{
    public function __construct(
        protected AIGovernanceService $governance
    ) {}

    /**
     * @param Collection|array $payloads normalized payloads from crawler aggregator
     * @param World|null $world optional world context to attach instances
     */
    public function ingest(Collection|array $payloads, ?World $world = null): void
    {
        $collection = $payloads instanceof Collection ? $payloads : collect($payloads);

        foreach ($collection as $payload) {
            $this->ingestSingle($payload, $world);
        }
    }

    private function ingestSingle(array $payload, ?World $world): void
    {
        $material = Material::firstOrCreate(
            ['slug' => Str::slug($payload['name'])],
            [
                'name' => $payload['name'],
                'ontology' => $this->guessOntology($payload),
                'function' => $this->guessFunction($payload),
                'origin_sources' => [$payload['origin_locale'] => $payload['source_url']],
            ]
        );

        $this->governance->logGeneration(
            $world?->id,
            'MATERIAL_INGESTION',
            json_encode($payload),
            'Material: '.$material->id,
            'ACCEPTED',
            null,
            1
        );

        if ($world) {
            $instance = MaterialInstance::firstOrCreate(
                [
                    'material_id' => $material->id,
                    'world_id' => $world->id,
                ],
                [
                    'label' => $payload['name'],
                    'origin_world_affinity' => $payload['world_affinity'],
                    'strength_level' => $this->guessStrength($payload),
                    'activation_epoch' => 0,
                ]
            );

            Log::info('Material instance ingested', [
                'material' => $material->id,
                'world' => $world->id,
                'instance' => $instance->id,
            ]);
        }
    }

    private function guessOntology(array $payload): MaterialOntology
    {
        $category = $payload['category'] ?? '';

        return match(true) {
            str_contains($category, 'herb') => MaterialOntology::BIOLOGICAL,
            str_contains($category, 'mineral') => MaterialOntology::MINERAL,
            str_contains($category, 'artifact') => MaterialOntology::ARTIFACT,
            default => MaterialOntology::UNKNOWN,
        };
    }

    private function guessFunction(array $payload): MaterialFunction
    {
        $tags = $payload['power_tags'] ?? [];

        if (in_array('healing', $tags)) {
            return MaterialFunction::RECOVERY;
        }

        if (in_array('weapon', $tags)) {
            return MaterialFunction::WEAPON;
        }

        if (in_array('buff', $tags)) {
            return MaterialFunction::AUGMENTATION;
        }

        return MaterialFunction::UNKNOWN;
    }

    private function guessStrength(array $payload): int
    {
        return match($payload['rarity'] ?? null) {
            'legendary' => 5,
            'epic' => 4,
            'rare' => 3,
            'uncommon' => 2,
            'common' => 1,
            default => 2,
        };
    }
}
