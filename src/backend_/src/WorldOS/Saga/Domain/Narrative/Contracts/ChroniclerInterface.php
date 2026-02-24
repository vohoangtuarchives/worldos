<?php

namespace WorldOS\Saga\Domain\Narrative\Contracts;

use WorldOS\Legacy\Application\Cosmology\Entities\Universe;

interface ChroniclerInterface
{
    public function chronicle(Universe $universe): string;
}
