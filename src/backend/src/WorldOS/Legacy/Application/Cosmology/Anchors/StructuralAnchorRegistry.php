<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Anchors;

use WorldOS\Legacy\Domain\Cosmology\Contracts\StructuralAnchorInterface;

class StructuralAnchorRegistry
{
    /** @var array<string, StructuralAnchorInterface> */
    private static array $anchors = [];

    public static function register(StructuralAnchorInterface $anchor): void
    {
        self::$anchors[$anchor->getKey()] = $anchor;
    }

    public static function get(string $key): ?StructuralAnchorInterface
    {
        if (self::$anchors === []) {
            self::defaults();
        }
        return self::$anchors[$key] ?? null;
    }

    /** @return array<string, StructuralAnchorInterface> */
    public static function all(): array
    {
        if (self::$anchors === []) {
            self::defaults();
        }
        return self::$anchors;
    }

    private static function defaults(): void
    {
        self::register(new AcademicAnchor());
        self::register(new FactionAnchor());
        self::register(new CommercialAnchor());
    }
}
