<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Constant;

/** Resolves preset key to PresetDescriptor. Default: only cosmology_legacy. */
final class PresetRegistry
{
    public function get(string $key): PresetDescriptor
    {
        return PresetDescriptor::default();
    }
}


