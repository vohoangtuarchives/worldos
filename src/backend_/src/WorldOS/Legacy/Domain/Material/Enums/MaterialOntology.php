<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Material\Enums;

enum MaterialOntology: string
{
    case SYMBOLIC = 'symbolic';
    case INSTITUTIONAL = 'institutional';
    case BEHAVIORAL = 'behavioral';
    case STRUCTURAL = 'structural';
    case BIOLOGICAL = 'biological';
    case MINERAL = 'mineral';
    case ARTIFACT = 'artifact';
    case UNKNOWN = 'unknown';
}
