<?php

namespace App\Domains\Narrative\Contracts;

use App\Domains\Cosmology\Entities\Universe;

interface ChroniclerInterface
{
    public function chronicle(Universe $universe): string;
}
