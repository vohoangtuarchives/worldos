<?php

namespace App\Domains\Material\Enums;

enum MaterialOntology: string
{
    case SYMBOLIC = 'symbolic';
    case INSTITUTIONAL = 'institutional';
    case BEHAVIORAL = 'behavioral';
    case STRUCTURAL = 'structural';
}
