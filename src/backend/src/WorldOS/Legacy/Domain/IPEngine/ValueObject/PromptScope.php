<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\IPEngine\ValueObject;

/**
 * Scope for an AI prompt (arc, chapter, world).
 * Domain-only.
 */
enum PromptScope: string
{
    case ARC = 'arc';
    case CHAPTER = 'chapter';
    case WORLD = 'world';
}
