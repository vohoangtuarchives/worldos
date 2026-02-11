<?php

namespace App\Domains\Genre\DTO;

class StoryEvent
{
    public function __construct(
        public string $type, // combat | death | resurrection | breakthrough ...
        public array $payload // ['attacker' => uuid, 'defender' => uuid, ...]
    ) {}
}
