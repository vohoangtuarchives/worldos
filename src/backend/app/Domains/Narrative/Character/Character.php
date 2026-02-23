<?php

namespace App\Domains\Narrative\Character;

use WorldOS\Saga\Domain\Narrative\ValueObject\EmotionState;
use App\Domains\Narrative\Character\Entities\Memory;
use Illuminate\Support\Collection;

// Aggregate Root
class Character
{
    protected string $id;
    protected string $name;
    protected MemoryCollection $memories;
    protected Collection $emotions; // Collection of EmotionState
    protected GoalStack $goals;

    public function __construct(
        string $id,
        string $name,
        MemoryCollection $memories,
        array $emotions, // [type => EmotionState]
        GoalStack $goals
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->memories = $memories;
        $this->emotions = collect($emotions);
        $this->goals = $goals;
    }

    // --- State Mutations (Domain Logic) ---

    public function remember(Memory $memory): void
    {
        // Future: Check constraints (Consistency Guard)
        $this->memories->add($memory);
    }

    public function feel(string $type, float $delta): void
    {
        $current = $this->emotions->get($type);

        if ($current) {
            $newEmotion = $current->amplify($delta);
        } else {
            // New emotion starts basic
            $newEmotion = new EmotionState($type, min(1.0, $delta), 0.1); 
        }

        $this->emotions->put($type, $newEmotion);
    }

    public function decayEmotions(): void
    {
        $this->emotions = $this->emotions->map(fn (EmotionState $e) => $e->decay());
    }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getMemories(): MemoryCollection { return $this->memories; }
    public function getEmotions(): Collection { return $this->emotions; }
    public function getGoals(): GoalStack { return $this->goals; }
}
