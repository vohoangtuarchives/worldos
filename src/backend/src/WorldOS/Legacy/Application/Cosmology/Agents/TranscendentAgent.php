<?php

namespace WorldOS\Legacy\Application\Cosmology\Agents;

use Illuminate\Support\Str;

class TranscendentAgent
{
    private string $id;
    private string $name;
    private string $type; // 'sovereign', 'rebel', 'visionary', 'warlord'
    private InfluenceField $influenceField;
    private float $power = 1.0;

    public function __construct(string $name, string $type, InfluenceField $influence, ?string $id = null)
    {
        $this->id = $id ?? (string) Str::uuid();
        $this->name = $name;
        $this->type = $type;
        $this->influenceField = $influence;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getType(): string
    {
        return $this->type;
    }

    public function getInfluenceField(): InfluenceField
    {
        return $this->influenceField;
    }

    public function exertInfluence(array $universes): void
    {
        // Agent exerts influence on a set of universes
        // In future: logic to select which universe based on resonance
        foreach ($universes as $universe) {
            // Check resonance or just apply blindly for now
            $universe->applyAgentInfluence($this);
        }
    }
}
