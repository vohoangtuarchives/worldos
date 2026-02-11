<?php

namespace App\StoryEngine;

class FactionState
{
    public string $id;
    public string $name;
    public string $type; // Sect, Clan, Kingdom, Guild
    public FactionMemory $memory;
    public Economy $economy; // Phase X
    public int $cohesion = 80; // 0-100

    public function __construct(string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->memory = new FactionMemory($id);
        $this->economy = new Economy(rand(80, 150));
    }
}
