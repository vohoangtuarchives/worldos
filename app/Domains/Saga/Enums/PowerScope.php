<?php

namespace App\Domains\Saga\Enums;

enum PowerScope: string
{
    case LOCAL = 'local';         // Single city, minimal global ledger impact
    case REGIONAL = 'regional';   // Single province/nation
    case GLOBAL = 'global';       // Affects the entire world ledger
    case TRANS_WORLD = 'trans';   // Affects multiple worlds/planes
}
