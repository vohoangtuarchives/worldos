<?php

declare(strict_types=1);

namespace App\Modules\Narrative\ValueObjects;

/**
 * Chapter Status — lifecycle of a serial chapter.
 *
 * From docs §11.2: DRAFT → NEEDS_REVIEW → APPROVED → CANONIZED
 */
enum ChapterStatus: string
{
    case DRAFT = 'draft';
    case NEEDS_REVIEW = 'needs_review';
    case APPROVED = 'approved';
    case CANONIZED = 'canonized';

    public function canReview(): bool
    {
        return $this === self::DRAFT || $this === self::NEEDS_REVIEW;
    }

    public function canApprove(): bool
    {
        return $this === self::NEEDS_REVIEW;
    }

    public function canCanonize(): bool
    {
        return $this === self::APPROVED;
    }
}
