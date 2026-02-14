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
        ];
    }
}
