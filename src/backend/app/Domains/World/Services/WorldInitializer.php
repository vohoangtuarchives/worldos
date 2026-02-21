<?php

declare(strict_types=1);

namespace App\Domains\World\Services;

use App\Domains\World\Aggregates\WorldAggregate;
use Tuzy\Domain\World\ValueObject\EntropyScore;
use Tuzy\Domain\World\ValueObject\GeneVector;
use App\Domains\Character\Aggregates\CharacterSurvivalAggregate;
use Tuzy\Domain\Character\ValueObject\NarrativeWeight;
use App\Domains\Character\Repositories\CharacterSurvivalRepository;
use Illuminate\Support\Facades\Log;

final class WorldInitializer
{
    public function __construct(
        private readonly CharacterSurvivalRepository $characterRepository,
    ) {}

    public function create(array $config): WorldAggregate
    {
        $this->validateConfig($config);

        $world = $this->initializeWorld($config);
        $this->initializeCharacters($world, $config);

        Log::info('World created', [
            'world_id' => $world->id(),
            'name' => $config['name'],
            'preset' => $config['preset'],
            'autonomous' => $config['autonomous'],
        ]);

        return $world;
    }

    private function validateConfig(array $config): void
    {
        $validator = \Validator::make($config, [
            'name' => 'required|string|max:255',
            'preset' => 'required|string|in:martial,immortal,apocalypse,tech,myth',
            'autonomous' => 'boolean',
            'entropy' => 'numeric|min:0|max:1',
            'tick' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid world configuration: ' . $validator->errors()->first());
        }
    }

    private function initializeWorld(array $config): WorldAggregate
    {
        $geneVector = $this->createGeneVectorFromPreset($config['preset']);
        
        return WorldAggregate::create(
            name: $config['name'],
            preset: $config['preset'],
            geneVector: $geneVector,
            entropy: new EntropyScore($config['entropy'] ?? 0.0),
            tick: $config['tick'] ?? 0,
            autonomous: $config['autonomous'] ?? false,
        );
    }

    private function initializeCharacters(WorldAggregate $world, array $config): void
    {
        $characters = $this->generateInitialCharacters($world, $config['preset']);
        
        foreach ($characters as $character) {
            $this->characterRepository->save($character);
        }

        Log::info('Initial characters created', [
            'world_id' => $world->id(),
            'character_count' => count($characters),
        ]);
    }

    private function generateInitialCharacters(WorldAggregate $world, string $preset): array
    {
        return match ($preset) {
            'martial' => $this->generateMartialCharacters($world),
            'immortal' => $this->generateImmortalCharacters($world),
            'apocalypse' => $this->generateApocalypseCharacters($world),
            'tech' => $this->generateTechCharacters($world),
            'myth' => $this->generateMythCharacters($world),
            default => $this->generateDefaultCharacters($world),
        };
    }

    private function generateMartialCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // Main protagonist
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_protagonist",
            baseSurvivalRate: 0.9,
            plotArmorFactor: 1.2
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Master/Teacher
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_master",
            baseSurvivalRate: 0.7,
            plotArmorFactor: 0.8
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Rival
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_rival",
            baseSurvivalRate: 0.6,
            plotArmorFactor: 0.6
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Side characters
        for ($i = 1; $i <= 3; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_side_{$i}",
                baseSurvivalRate: 0.5,
                plotArmorFactor: 0.4
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function generateImmortalCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // Young cultivator
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_cultivator",
            baseSurvivalRate: 0.8,
            plotArmorFactor: 1.1
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Sect leader
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_sect_leader",
            baseSurvivalRate: 0.6,
            plotArmorFactor: 0.7
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Elder
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_elder",
            baseSurvivalRate: 0.5,
            plotArmorFactor: 0.5
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Fellow disciples
        for ($i = 1; $i <= 4; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_disciple_{$i}",
                baseSurvivalRate: 0.4,
                plotArmorFactor: 0.3
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function generateApocalypseCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // Survivor leader
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_leader",
            baseSurvivalRate: 0.7,
            plotArmorFactor: 1.0
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Scientist/Doctor
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_scientist",
            baseSurvivalRate: 0.5,
            plotArmorFactor: 0.6
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Scavenger
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_scavenger",
            baseSurvivalRate: 0.6,
            plotArmorFactor: 0.5
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Other survivors
        for ($i = 1; $i <= 5; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_survivor_{$i}",
                baseSurvivalRate: 0.3,
                plotArmorFactor: 0.2
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function generateTechCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // AI researcher
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_researcher",
            baseSurvivalRate: 0.8,
            plotArmorFactor: 1.1
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Corporation CEO
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_ceo",
            baseSurvivalRate: 0.6,
            plotArmorFactor: 0.7
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Hacker
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_hacker",
            baseSurvivalRate: 0.7,
            plotArmorFactor: 0.6
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Tech workers
        for ($i = 1; $i <= 4; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_tech_{$i}",
                baseSurvivalRate: 0.5,
                plotArmorFactor: 0.4
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function generateMythCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // Chosen one
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_chosen",
            baseSurvivalRate: 0.9,
            plotArmorFactor: 1.3
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Oracle/Seer
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_oracle",
            baseSurvivalRate: 0.6,
            plotArmorFactor: 0.8
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Guardian
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_guardian",
            baseSurvivalRate: 0.7,
            plotArmorFactor: 0.9
        )->updateNarrativeWeight(NarrativeWeight::supporting());

        // Villagers
        for ($i = 1; $i <= 3; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_villager_{$i}",
                baseSurvivalRate: 0.4,
                plotArmorFactor: 0.3
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function generateDefaultCharacters(WorldAggregate $world): array
    {
        $characters = [];

        // Main character
        $characters[] = CharacterSurvivalAggregate::create(
            characterId: "{$world->id()}_main",
            baseSurvivalRate: 0.8,
            plotArmorFactor: 1.0
        )->updateNarrativeWeight(NarrativeWeight::main());

        // Supporting characters
        for ($i = 1; $i <= 2; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_support_{$i}",
                baseSurvivalRate: 0.6,
                plotArmorFactor: 0.5
            )->updateNarrativeWeight(NarrativeWeight::supporting());
        }

        // Minor characters
        for ($i = 1; $i <= 3; $i++) {
            $characters[] = CharacterSurvivalAggregate::create(
                characterId: "{$world->id()}_minor_{$i}",
                baseSurvivalRate: 0.4,
                plotArmorFactor: 0.3
            )->updateNarrativeWeight(NarrativeWeight::minor());
        }

        return $characters;
    }

    private function createGeneVectorFromPreset(string $preset): GeneVector
    {
        return match ($preset) {
            'martial' => GeneVector::martial(),
            'immortal' => GeneVector::immortal(),
            'apocalypse' => GeneVector::apocalypse(),
            'tech' => GeneVector::tech(),
            'myth' => GeneVector::myth(),
            default => GeneVector::martial(),
        };
    }
}
