<?php

/**
 * Character System Usage Examples
 * 
 * Demonstrates how to generate characters from materials and use them in narratives.
 */

namespace App\Examples;

use App\StoryEngine\Character\CharacterGenerator;
use Tuzy\Domain\Material\Contracts\MaterialRepositoryInterface;
use App\Models\World;

class CharacterSystemExample
{
    /**
     * Example 1: Generate a character from a material instance
     */
    public function generateCharacterFromMaterial()
    {
        $repository = app(MaterialRepositoryInterface::class);
        $generator = app(CharacterGenerator::class);

        // Get a material instance (e.g., DIVINE_KINGSHIP)
        $instance = $repository->getInstancesForWorld('some-world-id')->first();

        // Generate character
        $character = $generator->generateFromMaterial($instance);

        echo "Generated Character:\n";
        echo "  Name: {$character->name}\n";
        echo "  Archetype: {$character->archetype}\n";
        echo "  Traits: " . implode(', ', $character->traits) . "\n";
        echo "  Roles: " . implode(', ', $character->narrativeRoles) . "\n";
        echo "  Description: {$character->getDescription()}\n";

        return $character;
    }

    /**
     * Example 2: Generate multiple characters for a world
     */
    public function generateWorldCharacters(World $world)
    {
        $generator = app(CharacterGenerator::class);

        // Generate up to 10 characters from active materials
        $characters = $generator->generateFromWorld($world->id, 10);

        echo "Generated {count($characters)} characters for world {$world->name}:\n\n";

        foreach ($characters as $character) {
            echo "- {$character->name} ({$character->archetype})\n";
            echo "  Source: {$character->sourceMaterial}\n";
            echo "  Traits: " . implode(', ', $character->traits) . "\n";
            echo "\n";
        }

        return $characters;
    }

    /**
     * Example 3: Generate character-based narrative
     */
    public function generateCharacterNarrative()
    {
        $generator = app(CharacterGenerator::class);
        $repository = app(MaterialRepositoryInterface::class);

        $instance = $repository->getInstancesForWorld('some-world-id')->first();
        $character = $generator->generateFromMaterial($instance);

        $narrative = $generator->generateCharacterNarrative($character);

        echo "Character Narrative:\n";
        echo "  {$narrative}\n";

        return $narrative;
    }

    /**
     * Example 4: Link characters to material events
     */
    public function linkCharactersToEvents(World $world)
    {
        $generator = app(CharacterGenerator::class);
        $eventGenerator = app(\App\StoryEngine\Material\MaterialEventGenerator::class);

        // Generate characters
        $characters = $generator->generateFromWorld($world->id, 5);

        // Generate events
        $events = $eventGenerator->generateEvents($world);

        echo "Linking characters to events:\n\n";

        foreach ($characters as $character) {
            $linkedEvents = $generator->linkToEvents($character, $events);

            if (!empty($linkedEvents)) {
                echo "{$character->name} is involved in:\n";
                foreach ($linkedEvents as $event) {
                    echo "  - {$event['narrative']}\n";
                }
                echo "\n";
            }
        }

        return $characters;
    }

    /**
     * Example 5: Complete character-driven story generation
     */
    public function generateCharacterDrivenStory(World $world)
    {
        $generator = app(CharacterGenerator::class);
        $eventGenerator = app(\App\StoryEngine\Material\MaterialEventGenerator::class);

        // Generate characters from active materials
        $characters = $generator->generateFromWorld($world->id, 5);

        // Generate events
        $events = $eventGenerator->generateEvents($world);

        // Build story
        $story = "# World: {$world->name}\n\n";
        $story .= "## Characters\n\n";

        foreach ($characters as $character) {
            $narrative = $generator->generateCharacterNarrative($character);
            $story .= "### {$character->name}\n";
            $story .= "{$narrative}\n\n";
        }

        $story .= "## Events\n\n";

        foreach ($events as $event) {
            // Find characters involved in this event
            $involvedCharacters = array_filter($characters, function($char) use ($event) {
                return isset($event['material_code']) && $event['material_code'] === $char->sourceMaterial;
            });

            $story .= "**Epoch {$event['epoch']}**: {$event['narrative']}\n";

            if (!empty($involvedCharacters)) {
                $names = array_map(fn($c) => $c->name, $involvedCharacters);
                $story .= "  _(Involving: " . implode(', ', $names) . ")_\n";
            }

            $story .= "\n";
        }

        echo $story;

        return [
            'characters' => $characters,
            'events' => $events,
            'story' => $story,
        ];
    }
}
