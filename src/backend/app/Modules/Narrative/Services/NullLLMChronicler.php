<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Services;

use App\Modules\Narrative\Contracts\LLMChroniclerInterface;
use App\Modules\Shared\ValueObjects\WorldStateVector;
use App\WorldOS\Style\ValueObjects\GenreKey;

/**
 * Null LLM Chronicler — template-based stub.
 *
 * Generates deterministic text from state vector without actual LLM calls.
 * Used until a real LLM provider is configured.
 */
final class NullLLMChronicler implements LLMChroniclerInterface
{
    public function chronicle(
        WorldStateVector $state,
        GenreKey $genre,
        array $events = [],
        array $context = [],
    ): string {
        $vocabulary = $this->getVocabulary($genre);
        $lines = [];

        // Opening — based on entropy level
        if ($state->entropy > 0.7) {
            $lines[] = "The {$vocabulary['world']} trembled on the brink of {$vocabulary['chaos']}.";
        } elseif ($state->entropy < 0.3) {
            $lines[] = "A deep {$vocabulary['peace']} settled over the {$vocabulary['world']}.";
        } else {
            $lines[] = "The {$vocabulary['world']} continued its slow turn between {$vocabulary['order']} and {$vocabulary['chaos']}.";
        }

        // Cohesion narrative
        if ($state->cohesion > 0.7) {
            $lines[] = "The {$vocabulary['people']} stood united, their {$vocabulary['bond']} stronger than ever.";
        } elseif ($state->cohesion < 0.3) {
            $lines[] = "Fractures deepened among the {$vocabulary['people']}, old {$vocabulary['bond']}s fraying.";
        }

        // Innovation narrative
        if ($state->innovation > 0.7) {
            $lines[] = "Breakthroughs in {$vocabulary['knowledge']} opened new possibilities.";
        } elseif ($state->innovation < 0.2) {
            $lines[] = "The pursuit of {$vocabulary['knowledge']} had stagnated.";
        }

        // Recent events
        if (!empty($events)) {
            $lines[] = '';
            foreach (array_slice($events, 0, 3) as $event) {
                $lines[] = "» {$event}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, string>
     */
    private function getVocabulary(GenreKey $genre): array
    {
        return match ($genre) {
            GenreKey::XIANXIA => [
                'world' => 'cultivation realm',
                'chaos' => 'tribulation',
                'peace' => 'harmony of the Dao',
                'order' => 'heavenly mandate',
                'people' => 'cultivators',
                'bond' => 'sect alliance',
                'knowledge' => 'ancient arts',
            ],
            GenreKey::CYBERPUNK => [
                'world' => 'megacity',
                'chaos' => 'system crash',
                'peace' => 'digital silence',
                'order' => 'corporate control',
                'people' => 'citizens',
                'bond' => 'network connection',
                'knowledge' => 'bleeding-edge tech',
            ],
            GenreKey::COSMIC_HORROR => [
                'world' => 'fragile reality',
                'chaos' => 'the unknowable',
                'peace' => 'terrible stillness',
                'order' => 'false certainty',
                'people' => 'mortals',
                'bond' => 'shared delusion',
                'knowledge' => 'forbidden truth',
            ],
            default => [
                'world' => 'realm',
                'chaos' => 'chaos',
                'peace' => 'peace',
                'order' => 'order',
                'people' => 'people',
                'bond' => 'bond',
                'knowledge' => 'knowledge',
            ],
        };
    }
}
