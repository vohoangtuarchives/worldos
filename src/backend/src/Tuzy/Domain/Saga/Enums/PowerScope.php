<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\Enums;

enum PowerScope: string
{
    case LOCAL = 'local';
    case REGIONAL = 'regional';
    case GLOBAL = 'global';
    case TRANS_WORLD = 'trans';
}
