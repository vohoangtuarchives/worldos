<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use WorldOS\Society\Faction\ValueObject\Leader;
use WorldOS\Society\Faction\ValueObject\IdeologyVector;
use WorldOS\Society\Faction\ValueObject\PersonalityVector;
use WorldOS\Society\Faction\ValueObject\FactionMemory;

class Faction extends Model
{
    use HasFactory;

    protected $fillable = [
        'world_id', 
        'name', 
        'type', 
        'attributes',
        'leader_data',
        'ideology_vector',
        'personality_vector',
        'memory_state',
        'current_generation',
        'internal_cohesion'
    ];

    protected $casts = [
        'attributes' => 'array',
        'leader_data' => 'array',
        'ideology_vector' => 'array',
        'personality_vector' => 'array',
        'memory_state' => 'array',
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }

    public function historyLogs()
    {
        return $this->hasMany(FactionHistoryLog::class);
    }

    /**
     * Domain Object Accessors
     */

    public function getLeader(): Leader
    {
        return Leader::fromArray($this->leader_data ?? []);
    }

    public function getIdeology(): IdeologyVector
    {
        return IdeologyVector::fromArray($this->ideology_vector ?? []);
    }

    public function getPersonality(): PersonalityVector
    {
        return PersonalityVector::fromArray($this->personality_vector ?? []);
    }

    public function getMemory(): FactionMemory
    {
        return FactionMemory::fromArray($this->memory_state ?? []);
    }

    /**
     * Update State from Domain Objects
     */

    public function updateLeader(Leader $leader): void
    {
        $this->leader_data = $leader->toArray();
        $this->current_generation = $leader->generation;
    }

    public function updateIdeology(IdeologyVector $ideology): void
    {
        $this->ideology_vector = $ideology->toArray();
    }

    public function updatePersonality(PersonalityVector $personality): void
    {
        $this->personality_vector = $personality->toArray();
    }

    public function updateMemory(FactionMemory $memory): void
    {
        $this->memory_state = $memory->toArray();
    }
}
