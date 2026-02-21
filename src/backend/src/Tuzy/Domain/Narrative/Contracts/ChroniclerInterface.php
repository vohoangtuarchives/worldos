<?php

namespace Tuzy\Domain\Narrative\Contracts;

use Tuzy\Application\Cosmology\Entities\Universe;

interface ChroniclerInterface
{
    public function chronicle(Universe $universe): string;
}
