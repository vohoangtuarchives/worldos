<?php

namespace App\Domains\Genre\Genres\Xianxia;

use App\Domains\Genre\Contracts\GenreDefinition;
use App\Domains\Genre\Contracts\MaterialSystem;
use App\Domains\Genre\Contracts\ProgressionRule;
use App\Domains\Genre\Contracts\VocabularyMap;
use App\Domains\Genre\Contracts\EventCatalog;
use App\Domains\Genre\Contracts\GenreValidator;
use App\Domains\Genre\Validation\XianxiaGenreValidator;

class XianxiaDefinition implements GenreDefinition
{
    public function key(): string 
    { 
        return 'xianxia'; 
    }

    public function displayName(): string 
    { 
        return 'Xianxia (Cultivation)'; 
    }

    public function materials(): MaterialSystem 
    {
        return new QiMaterialSystem();
    }

    public function progression(): ProgressionRule 
    {
        return new CultivationProgression();
    }

    public function vocabulary(): VocabularyMap 
    {
        return new XianxiaVocabulary();
    }

    public function events(): EventCatalog 
    {
        return new XianxiaEventCatalog();
    }

    public function validator(): GenreValidator
    {
        return new XianxiaGenreValidator();
    }

    public function worldConstraints(): array 
    {
        return [
            'mortal_cannot_harm_immortal' => true,
            'flight_requires_foundation_establishment' => true,
            'soul_exists_separately' => true,
            'karma_is_real' => true,
            'karma_is_real' => true,
        ];
    }

    public function getNarrativePrompt(): string
    {
        return "Rewrite the following historical event in the style of a Xianxia/Cultivation novel (like 'Renegade Immortal'). Use terms like 'Qi', 'Dao', 'Tribulation', 'Realms'. Emphasize the scale of power, the ruthlessness of the cultivation world, and the insignificance of mortals.";
    }

    public function getPhysicsBias(): array
    {
        return [
            'order_bias' => 0.8,
            'chaos_sensitivity' => 0.2,
            'entropy_decay_rate' => 0.05,
            'innovation_burst_probability' => 0.1,
            'hierarchy_rigidity' => 0.9,
        ];
    }
}
