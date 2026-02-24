<?php

declare(strict_types=1);

namespace App\WorldOS\Style\ValueObjects;

/**
 * Genre Key — enumeration of supported narrative genres.
 *
 * Each genre maps to a default StyleVector (genre→physics).
 * From docs §2.3: Rút gọn về 8 Archetypes lõi.
 */
enum GenreKey: string
{
    case XIANXIA = 'xianxia';
    case CYBERPUNK = 'cyberpunk';
    case FANTASY = 'fantasy';
    case HISTORICAL = 'historical';
    case POSTAPOCALYPTIC = 'postapocalyptic';
    case STEAMPUNK = 'steampunk';
    case COSMIC_HORROR = 'cosmic_horror';
    case SPACE_OPERA = 'space_opera';

    /**
     * Default StyleVector for this genre.
     * ontology: 0=tech, 1=magic | epistemic: 0=empirical, 1=mystical
     * civilization: 0=tribal, 0.5=feudal, 1=networked | energy: 0=scarce, 1=abundant
     */
    public function defaultStyleVector(): StyleVector
    {
        return match ($this) {
            self::XIANXIA => new StyleVector(
                ontology: 0.9,     // Heavily magical
                epistemic: 0.85,   // Mystical cultivation knowledge
                civilization: 0.4, // Sect-based feudal
                energy: 0.8,       // Linh khí abundant
            ),
            self::CYBERPUNK => new StyleVector(
                ontology: 0.1,     // Pure tech
                epistemic: 0.15,   // Empirical/hacking
                civilization: 0.9, // Hyper-networked megacorps
                energy: 0.3,       // Resources controlled/scarce for masses
            ),
            self::FANTASY => new StyleVector(
                ontology: 0.7,     // Magical with some natural
                epistemic: 0.6,    // Mix of lore and discovery
                civilization: 0.5, // Medieval feudal
                energy: 0.6,       // Moderate magical energy
            ),
            self::HISTORICAL => new StyleVector(
                ontology: 0.05,    // No magic
                epistemic: 0.2,    // Mostly empirical, some superstition
                civilization: 0.5, // Depends on era
                energy: 0.4,       // Limited natural resources
            ),
            self::POSTAPOCALYPTIC => new StyleVector(
                ontology: 0.15,    // Mostly tech ruins
                epistemic: 0.3,    // Lost knowledge
                civilization: 0.15,// Tribal scavengers
                energy: 0.1,       // Mạt pháp — extreme scarcity
            ),
            self::STEAMPUNK => new StyleVector(
                ontology: 0.2,     // Tech with slight esoteric
                epistemic: 0.35,   // Victorian empiricism
                civilization: 0.65,// Industrial society
                energy: 0.5,       // Steam-powered moderate
            ),
            self::COSMIC_HORROR => new StyleVector(
                ontology: 0.6,     // Eldritch/unknowable
                epistemic: 0.9,    // Knowledge is dangerous
                civilization: 0.3, // Isolated communities
                energy: 0.7,       // Cosmic energy, incomprehensible
            ),
            self::SPACE_OPERA => new StyleVector(
                ontology: 0.3,     // Sci-fi with some psionics
                epistemic: 0.25,   // Advanced science
                civilization: 0.85,// Galactic federations
                energy: 0.75,      // Abundant fusion/exotic
            ),
        };
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::XIANXIA => 'Tu Tiên / Xianxia',
            self::CYBERPUNK => 'Cyberpunk',
            self::FANTASY => 'High Fantasy',
            self::HISTORICAL => 'Historical',
            self::POSTAPOCALYPTIC => 'Post-Apocalyptic',
            self::STEAMPUNK => 'Steampunk',
            self::COSMIC_HORROR => 'Cosmic Horror',
            self::SPACE_OPERA => 'Space Opera',
        };
    }
}
