<?php

namespace App\Domains\Faction\ValueObjects;

use Illuminate\Support\Str;

class Leader
{
    public function __construct(
        public string $name,
        public int $age,
        public int $generation,
        public PersonalityVector $personality,
        public string $title = 'Leader',
        public array $conversations = [],
        public array $relationships = [],
        public array $inventory = [],
        public array $quirks = [],
    ) {}

    public static function create(int $generation = 1, ?PersonalityVector $parentPersonality = null): self
    {
        $personality = $parentPersonality 
            ? $parentPersonality->inherit($parentPersonality) 
            : PersonalityVector::random();

        return new self(
            self::generateName(),
            mt_rand(20, 50), // Start age
            $generation,
            $personality,
            'Leader',
            [], // conversations
            [], // relationships
            [], // inventory
            self::generateQuirks() // quirks
        );
    }

    private static function generateQuirks(): array
    {
        $potential = ['Sợ độ cao', 'Thích rượu', 'Nói lắp', 'Mê tín', 'Yêu thơ ca', 'Nóng tính', 'Đa nghi'];
        $count = rand(0, 2);
        if ($count === 0) return [];
        
        $keys = array_rand($potential, $count);
        if (!is_array($keys)) $keys = [$keys];
        
        return array_map(fn($k) => $potential[$k], $keys);
    }

    private static function generateName(): string
    {
        $prefixes = ['Al', 'Bar', 'Cor', 'Dra', 'Eri', 'Fen', 'Gal', 'Hor', 'Ith', 'Jor'];
        $suffixes = ['aric', 'bor', 'cus', 'dorn', 'en', 'fryn', 'gorn', 'horn', 'ius', 'kan'];
        
        return $prefixes[array_rand($prefixes)] . $suffixes[array_rand($suffixes)];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'age' => $this->age,
            'generation' => $this->generation,
            'title' => $this->title,
            'personality' => $this->personality->toArray(),
            'conversations' => $this->conversations,
            'relationships' => $this->relationships,
            'inventory' => $this->inventory,
            'quirks' => $this->quirks,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? 'Unknown',
            $data['age'] ?? 30,
            $data['generation'] ?? 1,
            PersonalityVector::fromArray($data['personality'] ?? []),
            $data['title'] ?? 'Leader',
            $data['conversations'] ?? [],
            $data['relationships'] ?? [],
            $data['inventory'] ?? [],
            $data['quirks'] ?? []
        );
    }

    public function age(): self
    {
        $this->age++;
        return $this;
    }
}
