<?php

namespace App\Domains\Genre\Genres\Survival;

use App\Domains\Genre\Contracts\EventCatalog;
use App\Domains\Genre\Contracts\GenreDefinition;
use App\Domains\Genre\Contracts\GenreValidator;
use App\Domains\Genre\Contracts\MaterialSystem;
use App\Domains\Genre\Contracts\ProgressionRule;
use App\Domains\Genre\Contracts\VocabularyMap;

class SurvivalGenre implements GenreDefinition
{
    public function key(): string
    {
        return 'survival'; // Matches user request
    }

    public function displayName(): string
    {
        return 'Sinh Tồn (Survival)';
    }

    public function materials(): MaterialSystem
    {
        return new ScarcityMaterial();
    }

    public function progression(): ProgressionRule
    {
        return new AttritionProgression();
    }

    public function vocabulary(): VocabularyMap
    {
        return new SurvivalNarrativeVocabulary();
    }

    public function events(): EventCatalog
    {
        return new SurvivalEventCatalog();
    }

    public function validator(): GenreValidator
    {
        return new SurvivalGenreValidator();
    }

    public function worldConstraints(): array
    {
        return [
            'death_is_final' => true,
            'no_resurrection' => true,
            'no_system' => true,
            'environment_is_hostile' => true,
            'magic_level' => 'none',
            'magic_level' => 'none',
        ];
    }

    public function getNarrativePrompt(): string
    {
        return "Rewrite the following historical event in the style of a gritty Survival/Apocalypse novel (like 'The Road'). Emphasize scarcity, desperation, the harshness of the environment, and the fragility of life.";
    }

    public function getPhysicsBias(): array
    {
        return [
            'order_bias' => 0.3,
            'chaos_sensitivity' => 0.7,
            'entropy_decay_rate' => -0.1, // Entropy increases faster
            'innovation_burst_probability' => 0.05,
            'resource_scarcity' => 0.9,
        ];
    }
}
