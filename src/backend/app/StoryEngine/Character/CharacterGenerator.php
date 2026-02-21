<?php

namespace App\StoryEngine\Character;

use Tuzy\Domain\Material\MaterialInstance;
use Tuzy\Domain\Material\Material;

class CharacterGenerator
{
    private MaterialCharacterAffinity $affinity;
    private CharacterTraitDeriver $traitDeriver;

    public function __construct(
        MaterialCharacterAffinity $affinity,
        CharacterTraitDeriver $traitDeriver
    ) {
        $this->affinity = $affinity;
        $this->traitDeriver = $traitDeriver;
    }

    /**
     * Generate a character from a material instance.
     */
    public function generateFromMaterial(MaterialInstance $instance): ?Character
    {
        $material = $instance->material;
        $archetype = $this->affinity->getRandomArchetype($material->code);

        if (!$archetype) {
            return null;
        }

        $traits = $this->traitDeriver->deriveTraits($material, $instance);
        $narrativeRoles = $this->affinity->getNarrativeRoles($material->code);
        $name = $this->generateName($archetype, $material->code);

        return new Character(
            name: $name,
            archetype: $archetype,
            traits: $traits,
            narrativeRoles: $narrativeRoles,
            sourceMaterial: $material->code,
            worldId: $instance->world_id,
            metadata: [
                'material_strength' => $instance->strength_level,
                'activation_epoch' => $instance->activation_epoch,
            ]
        );
    }

    /**
     * Generate multiple characters from active materials in a world.
     */
    public function generateFromWorld(string $worldId, int $limit = 10): array
    {
        $repository = app(\Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface::class);
        $instances = $repository->getInstancesForWorld($worldId);
        
        $activeInstances = $instances->filter(fn($i) => $i->activation_epoch !== null && !$i->retired_at);
        
        $characters = [];
        foreach ($activeInstances->take($limit) as $instance) {
            $character = $this->generateFromMaterial($instance);
            if ($character) {
                $characters[] = $character;
            }
        }

        return $characters;
    }

    /**
     * Generate a character name based on archetype.
     */
    private function generateName(string $archetype, string $materialCode): string
    {
        $nameTemplates = [
            'king' => ['Aldric', 'Theron', 'Casimir', 'Roderic', 'Valerian'],
            'priest' => ['Brother Matthias', 'Father Elias', 'High Priest Zephyr', 'Sister Celeste'],
            'prophet' => ['Isaiah', 'Ezekiel', 'Miriam the Seer', 'Jonah'],
            'hero' => ['Aric the Brave', 'Lyra Stormborn', 'Kael Ironheart', 'Thalia'],
            'trickster' => ['Loki', 'Reynard', 'Puck', 'Anansi'],
            'merchant' => ['Merchant Silas', 'Trader Mara', 'Guildmaster Tobias'],
            'rebel_leader' => ['Spartacus', 'Boudica', 'Wat Tyler', 'Toussaint'],
            'storyteller' => ['Old Bard Finn', 'Griot Amara', 'Elder Sage'],
        ];

        $names = $nameTemplates[$archetype] ?? ['Unknown'];
        return $names[array_rand($names)];
    }

    /**
     * Link characters to material-driven events.
     */
    public function linkToEvents(Character $character, array $events): array
    {
        $linkedEvents = [];

        foreach ($events as $event) {
            // Check if event is related to character's source material
            if (isset($event['material_code']) && $event['material_code'] === $character->sourceMaterial) {
                $linkedEvents[] = array_merge($event, [
                    'character_id' => $character->id,
                    'character_name' => $character->name,
                ]);
            }
        }

        return $linkedEvents;
    }

    /**
     * Generate character-based narrative.
     */
    public function generateCharacterNarrative(Character $character): string
    {
        $templates = [
            'king' => "{name}, bearing the weight of divine authority, rules with {trait1} and {trait2}.",
            'prophet' => "{name}, filled with visions of transformation, preaches with {trait1} fervor.",
            'hero' => "{name}, answering the call to adventure, faces trials with {trait1} courage.",
            'trickster' => "{name}, ever the boundary-crosser, disrupts order with {trait1} cunning.",
            'merchant' => "{name}, master of trade networks, navigates the market with {trait1} skill.",
            'rebel_leader' => "{name}, voice of the oppressed, rallies the people with {trait1} passion.",
        ];

        $template = $templates[$character->archetype] ?? "{name} embodies the essence of {archetype}.";

        return strtr($template, [
            '{name}' => $character->name,
            '{archetype}' => $character->archetype,
            '{trait1}' => $character->traits[0] ?? 'unknown',
            '{trait2}' => $character->traits[1] ?? 'mysterious',
        ]);
    }
}
