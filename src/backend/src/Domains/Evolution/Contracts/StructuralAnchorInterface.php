<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Contracts;

/**
 * Structural Anchor â€” Neo cáº¥u trÃºc.
 * Determines institutions, conflict topology, protagonist archetypes, resource flow.
 * Anchor must affect simulation topology and rules, not only flavor text.
 */
interface StructuralAnchorInterface
{
    public function getKey(): string;

    /** Institutions (e.g. councils, markets, factions) that define power centers. */
    public function generateInstitutions(): array;

    /** Conflict types and topology (who conflicts with whom, over what). */
    public function generateConflictTopology(): array;

    /** Core protagonist archetypes (e.g. student/master, leader/spy, merchant/broker). */
    public function protagonistArchetypes(): array;

    /** Resource flow model: primary resource type and distribution rules. */
    public function resourceFlowModel(): array;
}


